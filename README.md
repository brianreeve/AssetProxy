#AssetProxy

Super simple asset (image, audio, video, etc) proxy.

##Usage

	$loader = new \BrianReeve\AssetProxy\AssetLoader();
	$asset = $loader->getAsset('/absolute/path/to/file.ext');

	$asset->getMimeType();

	$asset->stream();

Or use a relative path

$loader = new \BrianReeve\AssetProxy\AssetLoader();

	$loader = new \BrianReeve\AssetProxy\AssetLoader('/base/path');
	$asset = $loader->getAsset('relative/path/to/file.ext');

	$asset->getMimeType();

	$asset->stream();