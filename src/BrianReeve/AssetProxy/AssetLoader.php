<?php

namespace BrianReeve\AssetProxy;

//use BrianReeve\AssetProxy\Asset;

class AssetLoader
{
	protected $_basePath = '/';

	public function __construct($basePath = NULL)
	{
		if (strtoupper(substr(PHP_OS, 0 , 3)) === 'WIN') {
			$this->_basePath = '';
		}

		if (!empty($basePath)) {
			$this->_basePath = $basePath;
		}
	}

	public function getAsset($path)
	{
		if (empty($this->_basePath) || strpos($path,'/') === 0) {
			$fullPath = $path;
		} else {
			$fullPath = $this->_basePath.$path;
		}
		return new Asset($fullPath);
	}


}