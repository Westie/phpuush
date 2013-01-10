<?php
/**
 *	User object for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


class User extends Element
{
	/**
	 *	Loads a user based on their username and password
	 */
	public function loadAuth($sEmailAddress, $sPassword)
	{
		$aArguments = array
		(
			"email_address" => $sEmailAddress,
			"password" => sha1($sPassword),
		);
		
		return $this->load($aArguments);
	}
	
	
	/**
	 *	Loads a user based on their API Key
	 */
	public function loadAPIKey($sAPIKey)
	{
		$aArguments = array
		(
			"api_key" => $sAPIKey,
		);
		
		return $this->load($aArguments);
	}
	
	
	/**
	 *	Returns how many bytes this user has uploaded so far.
	 */
	public function getTotalUploadedBytes()
	{
		$aFragments = array
		(
			"users_id" => $this->id,
			"is_deleted" => false,
		);
		
		$aResult = $this->pDatabase->fetch("SELECT sum(file_size) AS file_sum FROM [uploads] WHERE users_id = :users_id AND is_deleted = :is_deleted", $aFragments);
		
		if($aResult)
		{
			return (integer) $aResult[0]->file_sum;
		}
		
		return 0;
	}
}