<?php

namespace BrianReeve\AssetProxy;

use BrianReeve\MimeType\MimeType;

class Asset
{
	protected $_fullPath = '';

	protected $_mimeType = '';

	protected $_rawRequestRange = FALSE;
	protected $_range = NULL;

	public function __construct($fullPath)
	{
		$this->_fullPath = $fullPath;

		if ($this->canAccess()) {
			$this->_mimeType = MimeType::detect($fullPath);

			$this->_rawRequestRange = self::getRawRequestRange();
		}

		//$this->setLogger($psrLogger);
	}

	/*	public function setLogger($psrLogger)
		{
			$this->logger = $psrLogger;
		}
	*/

	public static function getRawRequestRange()
	{
		if (isset($_SERVER['HTTP_RANGE'])) {
			// IIS/Some Apache versions
			$range = $_SERVER['HTTP_RANGE'];
		} else if ($apache = apache_request_headers()) {
			// Try Apache again
			$headers = array();
			foreach ($apache as $header => $val) {
				$headers[strtolower($header)] = $val;
			}
			if (isset($headers['range'])) {
				$range = $headers['range'];
			} else {
				$range = FALSE;
			}
		} else {
			$range = FALSE;
		}

		return $range;
	}

	public function getRange()
	{
		if (empty($this->_range)) {
			$rangeArr = array(
				'start' => FALSE,
				'end' => FALSE,
				'length' => 0
			);

			// Get the data range requested (if any)
			if ($this->isPartial()) {
				$range = $this->_rawRequestRange;
				list($param,$range) = explode('=',$range);
				if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
					header("HTTP/1.1 400 Invalid Request");
					exit;
				}
				$range = explode(',',$range);
				$range = explode('-',$range[0]); // We only deal with the first requested range
				if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
					header("HTTP/1.1 400 Invalid Request");
					exit;
				}
				if ($range[0] === '') { // First number missing, return last $range[1] bytes
					$end = $this->getFilesize() - 1;
					$start = $end - intval($range[0]);
				} else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
					$start = intval($range[0]);
					$end = $this->getFilesize() - 1;
				} else { // Both numbers present, return specific range
					$start = intval($range[0]);
					$end = intval($range[1]);
				}

				if ($end >= $this->getFilesize() || (!$start && (!$end || $end == ($this->getFilesize() - 1)))) {
					//$partial = false; // Invalid range/whole file specified, return whole file
				}

				$rangeArr['start'] = $start;
				$rangeArr['end'] = $end;
				$rangeArr['length'] = $end - $start + 1;

				$this->_range = $rangeArr;
			}
		}

		return $this->_range;
	}

	public function canAccess()
	{
		return file_exists($this->_fullPath) && is_readable($this->_fullPath);
	}

	public function isPartial()
	{
		//return ($this->_range)?TRUE:FALSE;

		return $this->_rawRequestRange!==FALSE;

	}

	public function getFilesize()
	{
		return filesize($this->_fullPath);
	}

	public function getMimeType()
	{
		return $this->_mimeType;
	}

	public function setHeaders()
	{
		if ($this->canAccess() && $this->_mimeType) {
			header('Content-type: '.$this->_mimeType);
			header('Content-length: '.$this->getFilesize());
			header('Accept-Ranges: bytes');

			if ($this->isPartial()) {
				$rangeArr = $this->getRange();
				header('HTTP/1.1 206 Partial Content');
				header("Content-Range: bytes {$rangeArr['start']}-{$rangeArr['end']}/".$this->getFilesize());
			}
		}
	}

	public function stream()
	{
		@error_reporting(0);
		if ($this->canAccess() && $this->_mimeType) {
			$fh = @fopen($this->_fullPath, 'rb');
			if ($this->isPartial()) {
				// if requested, send extra headers and part of file...
				$rangeArr = $this->getRange();
				if ($rangeArr['start']) {
					fseek($fh,$rangeArr['start']);
				}
				$length = $rangeArr['length'];
				$bytesLeftToRead = $rangeArr['length'];

			} else {
				$length = $this->getFilesize();
				$bytesLeftToRead = $this->getFilesize();
			}

			while ($bytesLeftToRead > 0) { // Read in blocks of 8KB so we don't chew up memory on the server
				$read = ($length > 8192) ? 8192 : $length;
				$bytesLeftToRead -= $read;
				print(fread($fh,$read));
			}
			fclose($fh);
			exit;
		}
	}
}