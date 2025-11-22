### Image Editing Feature Plan

1. **Objectives**
   - Allow users to crop and apply preset filters to any image inserted via the ASI Gutenberg block.
   - Actions occur after the search/download step, before the image is finalized in the post.

2. **User Flow**
   1. User opens the ASI block modal and searches for images (current flow).
   2. When clicking a result, the download happens as today but the modal stays open.
   3. A new “Edit Image” panel appears showing the downloaded image inside a cropper.
   4. User can adjust:
      - Crop (freeform + presets: square, 4:3, 16:9).
      - Filters (CSS presets: Clarendon, Gingham, Moon, 1977, etc.).
      - Optional sliders for brightness/contrast.
   5. “Apply” re-encodes the edited image (canvas) and sends it to the backend as a data URL along with metadata.
   6. Backend saves it to the media library and inserts/replaces the Gutenberg block as today.

3. **Frontend Architecture**
   - Extend `admin/blocks/asi-images/build/index.js`:
     - Add toolbar buttons (Crop, Filters) using `BlockControls`.
     - Integrate a React cropper (Cropper.js) loaded dynamically to avoid bundle bloat.
     - Maintain new state: `editingImage`, `cropData`, `activeFilter`, `editedBlob`.
     - Use canvas to apply filters and export to data URL before upload.
   - Offer two entry points:
     1. Immediately after selecting an image inside the modal.
     2. Later, when the user selects an ASI image block in the editor toolbar.

4. **Backend Adjustments**
   - Update `ASI_block_downloading_image` (in `admin/class-all-sources-images-admin.php`):
     - Accept a `data:image/*;base64` payload and skip remote download when provided.
     - Validate size limits and mime type.
   - Store additional metadata (`filter_used`, `crop_rect`) for analytics/logging.

5. **Dependencies**
   - Add Cropper.js (MIT) via npm; enqueue within block build.
   - Optional lightweight filter helper (e.g., custom CSS filter map) – no extra dependency.

6. **Telemetry & UX**
   - Log editing actions via existing `ASI_log` to help debug.
   - Show undo/redo inside modal (simple stack of previous states).

7. **Testing**
   - Manual in Gutenberg editor for featured-image and insert-into-content scenarios.
   - Ensure media library receives edited image with correct mime and metadata.
