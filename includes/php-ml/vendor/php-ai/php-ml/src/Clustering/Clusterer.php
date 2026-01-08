<?php
if ( ! defined( 'ABSPATH' ) ) exit;


declare(strict_types=1);

namespace Phpml\Clustering;

interface Clusterer
{
    public function cluster(array $samples): array;
}
