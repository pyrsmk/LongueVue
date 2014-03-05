<?php

/*
	A simple contents extractor

	Author
		Aurélien Delogu (dev@dreamysource.fr)
*/
class LongueVue{

	/*
		string $chain
		string $regex
		array $validators
		array $default_values
	*/
	protected $chain;
	protected $regex;
	protected $validators		= array();
	protected $default_values	= array();

	/*
		Constructor

		Parameters
			string $chain
	*/
	public function __construct($chain){
		$this->chain=(string)$chain;
	}

	/*
		Add a validator

		Parameters
			string $slug
			string $regex

		Return
			LongueVue
	*/
	public function addValidator($slug,$regex){
		$this->regex=null;
		$this->validators[(string)$slug]=(string)$regex;
		return $this;
	}

	/*
		Add a default value

		Parameters
			string $slug
			string $value

		Return
			LongueVue
	*/
	public function addDefaultValue($slug,$value){
		$this->regex=null;
		$this->default_values[(string)$slug]=(string)$value;
		return $this;
	}

	/*
		Verify if the chain matches

		Parameters
			string $chain

		Return
			false
			array
	*/
	public function match($contents){
		// Extract
		if(!preg_match($this->_compile(),$contents,$pieces)){
			return false;
		}
		// Init values
		$values=$this->default_values;
		// Loop over potential arguments
		foreach($pieces as $name=>$value){
			// Drop integer keys
			if(is_int($name)){
				continue;
			}
			// Verify value format
			$regex=$this->validators[$name];
			if($regex && $value && !preg_match('#^'.$regex.'$#S',$value)){
				return false;
			}
			// Save value
			if($value){
				$values[$name]=$value;
			}
		}
		// Return
		return $values;
	}

	/*
		Compile the regex from the base chain

		Return
			string
	*/
	protected function _compile(){
		if(!$this->regex){
			// Compile the chain
			$tokens=preg_split('#
					(?<!\\\)(
						\{[a-zA-Z_]\w+\}|
						\*|
						\+
					)
				#Ssx',
				$this->chain,
				null,
				PREG_SPLIT_DELIM_CAPTURE
			);
			foreach($tokens as $token){
				$name=substr($token,1,strlen($token)-2);
				// Named slug
				if($token{0}=='{'){
					$regex.='(?<'.$name.'>.*?)';
				}
				// Joker slug
				elseif($token=='*'){
					$regex.='.+?';
				}
				// Word slug
				elseif($token=='+'){
					$regex.='\w+?';
				}
				// Text
				else{
					$regex.=preg_quote(
						str_replace(
							array('\{','\*','\+'),
							array('{','*','+'),
							$token
						),
						'/'
					);
				}
				// Default value
				if($this->default_values[$name]){
					$regex.='?';
				}
			}
			$this->regex="/^$regex$/Ss";
		}
		return $this->regex;
	}

}
