<?php

########################################################### Prepare

error_reporting(E_ALL);

require __DIR__.'/../src/LongueVue.php';
require __DIR__.'/vendor/autoload.php';

$minisuite=new MiniSuite\Cli('LongueVue');
$minisuite->disableAnsiColors();

########################################################### Base tests

$minisuite->test('Dry',function(){
	$longuevue=new LongueVue('foobar');
	return $longuevue->match('foobar')===array();
});

$minisuite->test('One result',function(){
	$longuevue=new LongueVue('#{foo}#');
	return $longuevue->match('#...#')==
		   array('foo'=>'...');
});

$minisuite->test('Several results',function(){
	$longuevue=new LongueVue('/{foo}/{bar}/{foobar}');
	return $longuevue->match('/foo/bar/foobar')==
		   array('foo'=>'foo','bar'=>'bar','foobar'=>'foobar');
});

$minisuite->group('Escaping',function($minisuite){

	$minisuite->test('Match',function(){
		$longuevue=new LongueVue('\{foo}');
		return $longuevue->match('{foo}')===array();
	});

	$minisuite->test('Does not match',function(){
		$longuevue=new LongueVue('\{foo}');
		return $longuevue->match('bar')===false;
	});

});

$minisuite->group('Special',function($minisuite){

	$minisuite->test('+ match',function(){
		$longuevue=new LongueVue('#+#');
		return $longuevue->match('#abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#')===array();
	});

	$minisuite->test('+ does not match',function(){
		$longuevue=new LongueVue('+');
		return $longuevue->match('#foo#')===false;
	});

	$minisuite->test('* match',function(){
		$longuevue=new LongueVue('*');
		return $longuevue->match('#foo#')===array();
	});

});

########################################################### Validators

$minisuite->group('Validation',function($minisuite){

	$minisuite->test('Match',function(){
		$longuevue=new LongueVue('#{foo}#{bar}#');
		$longuevue->addValidator('foo','\d+');
		return $longuevue->match('#1234#5678#')===array('foo'=>'1234','bar'=>'5678');
	});

	$minisuite->test('Does not match',function(){
		$longuevue=new LongueVue('#{foo}#{bar}#');
		$longuevue->addValidator('foo','\d+');
		return $longuevue->match('#1234abcd#5678#')===false;
	});

});

########################################################### Default values

$minisuite->group('Default values',function($minisuite){

	$minisuite->test('Match',function(){
		$longuevue=new LongueVue('{foo}');
		$longuevue->addDefaultValue('foo','bar');
		return $longuevue->match('foo')===array('foo'=>'foo');
	});

	$minisuite->test('Does not match',function(){
		$longuevue=new LongueVue('##{foo}blah');
		$longuevue->addDefaultValue('foo','bar');
		return $longuevue->match('##blah')===array('foo'=>'bar');
	});

	$minisuite->test('Match with a validator',function(){
		$longuevue=new LongueVue('#{foo}#');
		$longuevue->addValidator('foo','\d+');
		$longuevue->addDefaultValue('foo','5678');
		return $longuevue->match('##')===array('foo'=>'5678');
	});

	$minisuite->test('Does not match with a validator',function(){
		$longuevue=new LongueVue('{foo}');
		$longuevue->addValidator('foo','\d+');
		$longuevue->addDefaultValue('foo','5678');
		return $longuevue->match('.1234.')===false;
	});

});

########################################################### Run tests

$minisuite->run();