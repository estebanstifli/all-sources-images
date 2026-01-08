<?php
if ( ! defined( 'ABSPATH' ) ) exit;


declare(strict_types=1);

namespace Phpml\Preprocessing\Imputer;

interface Strategy
{
    /**
     * @return mixed
     */
    public function replaceValue(array $currentAxis);
}
