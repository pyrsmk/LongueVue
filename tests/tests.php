<?php

########################################################### Prepare

error_reporting(E_ALL);

require __DIR__.'/../src/LongueVue.php';
require __DIR__.'/vendor/autoload.php';

$minisuite=new MiniSuite('LongueVue');

########################################################### Base tests

$longuevue=new LongueVue('foobar');
$minisuite->expects('Dry')
		  ->that($longuevue->match('foobar'))
		  ->isTheSameAs(array());

$longuevue=new LongueVue('#{foo}#');
$minisuite->expects('One result')
		  ->that($longuevue->match('#...#'))
		  ->isTheSameAs(array('foo'=>'...'));

$longuevue=new LongueVue('/{foo}/{bar}/{foobar}');
$minisuite->expects('Several results')
		  ->that($longuevue->match('/foo/bar/foobar'))
		  ->isTheSameAs(array('foo'=>'foo','bar'=>'bar','foobar'=>'foobar'));

$minisuite->group('Escaping',function($minisuite){

	$longuevue=new LongueVue('\{foo}');

	$minisuite->expects('Match')
			  ->that($longuevue->match('{foo}'))
			  ->isTheSameAs(array());

	$minisuite->expects('Does not match')
			  ->that($longuevue->match('bar'))
			  ->isTheSameAs(false);

});

$minisuite->group('Special',function($minisuite){

	$longuevue=new LongueVue('#+#');
	$minisuite->expects('Match')
			  ->that($longuevue->match('#abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#'))
			  ->isTheSameAs(array());


	$longuevue=new LongueVue('+');
	$minisuite->expects('+ does not match')
			  ->that($longuevue->match('#foo#'))
			  ->isTheSameAs(false);

	$longuevue=new LongueVue('*');
	$minisuite->expects('* matches')
			  ->that($longuevue->match('#foo#'))
			  ->isTheSameAs(array());

});

########################################################### Validators

$minisuite->group('Validation',function($minisuite){

	$longuevue=new LongueVue('#{foo}#{bar}#');
	$longuevue->addValidator('foo','\d+');

	$minisuite->expects('Match')
			  ->that($longuevue->match('#1234#5678#'))
			  ->isTheSameAs(array('foo'=>'1234','bar'=>'5678'));

	$minisuite->expects('Does not match')
			  ->that($longuevue->match('#1234abcd#5678#'))
			  ->isTheSameAs(false);

});

########################################################### Default values

$minisuite->group('Default values',function($minisuite){

	$longuevue=new LongueVue('{foo}');
	$longuevue->addDefaultValue('foo','bar');
	$minisuite->expects('Match')
			  ->that($longuevue->match('foo'))
			  ->isTheSameAs(array('foo'=>'foo'));


	$longuevue=new LongueVue('##{foo}blah');
	$longuevue->addDefaultValue('foo','bar');
	$minisuite->expects('Does not match')
			  ->that($longuevue->match('##blah'))
			  ->isTheSameAs(array('foo'=>'bar'));


	$longuevue=new LongueVue('#{foo}#');
	$longuevue->addValidator('foo','\d+');
	$longuevue->addDefaultValue('foo','5678');
	$minisuite->expects('Match with a validator')
			  ->that($longuevue->match('##'))
			  ->isTheSameAs(array('foo'=>'5678'));

	$longuevue=new LongueVue('{foo}');
	$longuevue->addValidator('foo','\d+');
	$longuevue->addDefaultValue('foo','5678');
	$minisuite->expects('Does not match with a validator')
			  ->that($longuevue->match('.1234.'))
			  ->isTheSameAs(false);

});