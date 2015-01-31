<?php

namespace BrianReeve\AssetProxy;

use BrianReeve\MimeType\MimeType;

class Asset
{
	protected $_fullPath = '';

	protected $_mimeType = '';

	public function __construct($fullPath)
	{
		$this->_fullPath = $fullPath;

		if ($this->canAccess()) {
			$this->_mimeType = MimeType::detect($fullPath);
		}
	}

	public function canAccess()
	{
		return file_exists($this->_fullPath) && is_readable($this->_fullPath);
	}

	public function getMimeType()
	{
		return $this->_mimeType;
	}

	public function setHeaders()
	{
		if ($this->canAccess() && $this->_mimeType) {
			header('Content-type: '.$this->_mimeType);
			header('Content-length: '.filesize($this->_fullPath));
		}
	}

	public function stream()
	{
		if ($this->canAccess() && $this->_mimeType) {
			$fh = @fopen($this->_fullPath, 'rb');
			if ($fh) {
				fpassthru($fh);
				fclose($fh);
				exit;
			}
		}
	}
}