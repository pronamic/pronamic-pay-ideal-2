<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/psr-4',
			__DIR__ . '/pronamic-pay-ideal-2.php',
		]
	)
	->withSkip(
		[
			ClassPropertyAssignToConstructorPromotionRector::class,
		]
	)
	->withPhpSets()
	->withTypeCoverageLevel( 0 )
	->withDeadCodeLevel( 0 )
	->withCodeQualityLevel( 0 );
