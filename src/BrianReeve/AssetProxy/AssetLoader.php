<?php

namespace BrianReeve\AssetProxy;

//use BrianReeve\AssetProxy\Asset;

class AssetLoader
{
	protected $_basePath = '';

	public function __construct($basePath)
	{
		$this->_basePath = $basePath;
	}

	public function getAsset($relativePath)
	{
		$fullPath = $this->_basePath.$relativePath;
		return new Asset($fullPath);
	}


}