<?php

class domains_like
{
	public function getWhere()
	{
		model::pointDomain();
	}
}

class back_comp
{

	public function comp_213_to_214()
	{
		$this->extensions['domains'] = new domains_like();
	}


}

?>