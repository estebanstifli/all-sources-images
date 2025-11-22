# Instant Images Masonry Study

## Scope
- Repository: `instant-images/`
- Focus files: `build/instant-images.js`, `build/style-instant-images.css`, `build/instant-images.asset.php`
- Goal: understand how Instant Images renders its Pinterest-style collage so we can reproduce the experience inside `All Sources Images`.

## Architecture Overview
- The admin UI is a React application bundled in `build/instant-images.js` (Webpack). It relies on:
  - `axios` (`bt` in the bundle) for API calls.
  - `imagesLoaded` (module `7943`, referenced as `Wr`) to detect when each thumbnail image has fully rendered.
  - `Masonry` (module `6994`, referenced as `St`) for the waterfall layout.
  - `IntersectionObserver` helpers (`At`) to lazily fade tiles in when they enter the viewport.
- UI state (provider, filters, photo data array `w`, loading flags, etc.) lives in React hooks so every search re-renders the same `<article class="photo">` tree.

## DOM Structure
- Gallery container sits inside `#photo-listing` with a child element referenced through `fe` (a `useRef`).
- Each tile is rendered as:
  ```html
  <article class="photo" data-provider="unsplash">
    <div class="photo-wrap photo-[status]">
      <div class="img-wrap">
        <button class="photo-upload" data-id="...">
          <img src="..." ref={imgRef} />
        </button>
      </div>
      <div class="photo-controls">
        <!-- author info, CTA buttons, status badges -->
      </div>
    </div>
  </article>
  ```
- Ads/featured items reuse the same `.photo` base but add `.feature` and custom content.

## CSS Layout Mechanics (`build/style-instant-images.css`)
- `#photos` intentionally stretches wider than its parent (`width: calc(100% + 10px); margin-left: -5px;`) to create even gutters on both sides.
- `.photo` defaults to `width: 20%` (five columns) with responsive breakpoints:
  - `25%` below 1500px, `33.33%` below 1270px, `50%` below 800px, and full width below 600px.
- Tiles start at `opacity: 0` and transition to `opacity: 1` once `.photo.in-view` is applied.
- Thumbnails are wrapped in `.img-wrap` + `.photo-upload button` so hover/click overlays can be controlled purely with CSS.
- Overlays (`.photo-meta`, `.photo-controls`, `.fade`) use gradients and absolute positioning; Masonry only governs geometry, not these UI states.

## Masonry + imagesLoaded Flow
- `Wr` is the imported `imagesLoaded` helper. Every time the `w` photo array changes, a `useEffect` runs:
  ```js
  useEffect(() => {
      Wr(fe.current, () => {
          if (!ve) {
              de.current = new (St())(fe.current, { itemSelector: '.photo' });
              fe.current.querySelectorAll('.photo').forEach((el) => el.classList.add('in-view'));
          }
          setTimeout(() => { T(false); I(false); if (!B) L(true); }, 250);
      });
  }, [w]);
  ```
  - `fe` is the gallery root.
  - `ve` flags “block”/sidebar contexts where Masonry was already created.
  - `de` stores the Masonry instance for later refreshes (not shown in snippet but used elsewhere when appending new photos).
  - `T`/`I`/`L` flip loading and UI states once layout stabilizes.
- On the initial run, the code instantiates Masonry with only `itemSelector: '.photo'`. Column widths come from CSS (`.photo { width: 20%; }`), so Masonry uses the first element’s width as the column baseline. Gutters are also purely CSS (`padding: 0 3px 6px`).
- Every `.photo` receives the `.in-view` class right after layout, which triggers the opacity transition defined in CSS.
- Subsequent mutations (`load more` or provider switch) reuse the existing Masonry instance—new nodes are appended, and Masonry is told to lay them out (see the `ve && we()` branch just before the effect cleanup).

## Image Loading & Visibility
- `imagesLoaded` prevents Masonry from measuring items before the browser knows their heights. This avoids the “tiny square” issue we see today.
- There’s also an `IntersectionObserver` utility (`At`) that attaches to each tile. Once a tile intersects the viewport, it toggles CSS classes responsible for hover affordances and lazy opacity; this doubles as a performance guard on massive result sets.

## Data & Interaction Notes
- Each `photo` component wires multiple dataset attributes (`data-id`, `data-url`, `data-title`, etc.) so downloading or inserting into the post editor can be handled by shared button handlers.
- Buttons manipulate state flags like `photo-uploading`, `photo-success`, etc., which map to CSS selectors (`.photo-wrap.photo-success button.photo-upload` to dim the thumbnail, show spinners, etc.).
- The load-more footer (`.load-more-wrap`) reuses the same gallery container; new results are concatenated into `w`, triggering the `imagesLoaded + Masonry` effect again.

## Takeaways for All Sources Images
1. **Wait for image heights.** Use `imagesLoaded` (or `Promise.all` on image `load` events) before asking Masonry/grid logic to compute spans.
2. **Let CSS set the column math.** Instant Images keeps Masonry config minimal—tile width, gutters, and media queries live in CSS, so JS only has to tell Masonry which selector to manage.
3. **Persist a Masonry instance.** Store the instance in a ref so you can call `appended()` / `layout()` when new images arrive instead of rebuilding from scratch.
4. **Fade items via classes.** Tiles begin hidden and only become visible once both image loading and Masonry layout complete, eliminating the “shifting thumbnails” flash.
5. **Encapsulate overlays.** Author labels, CTA buttons, and hover effects stay within `.photo-controls`, independent of the geometry system—useful when porting the same chrome to our block.

By mirroring this sequence (load images → run Masonry on a known container → toggle visibility classes), we should be able to achieve the same Pinterest-style collage inside the All Sources Images block.
