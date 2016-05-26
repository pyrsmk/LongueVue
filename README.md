LongueVue 0.1.6
===============

LongueVue is a contents extractor built on top of `preg_match()`. Concretely, you can extract any string contents from anything, like discover articles on some blog to create a RSS stream per example.

Installing
----------

Pick up the source or install it with [Composer](https://getcomposer.org/) :

```
composer require pyrsmk/longuevue
```

Matching and extracting
-----------------------

The pattern is a chain with `{var}` variables. If the chain matches, then the values are extracted :

```php
$longuevue=new LongueVue('/articles/{id}/comments');
// Will return false
$longuevue->match('/articles');
// Will return false too
$longuevue->match('/articles//comments');
// Will return array('id'=>'72')
$longuevue->match('/articles/72/comments');
// Will return array()
$longuevue->match('/articles//comments');
```

Validators
----------

You can add a validator to the engine for a specific value. If that value does not match the regex validator, then the entire chain won't match at all.

```php
$longuevue=new LongueVue('/articles/{id}/comments');
$longuevue->addValidator('id','\d+');
// Match
$longuevue->match('/articles/72/comments');
// Won't match
$longuevue->match('/articles/some_article/comments');
```

Default values
--------------

Also, if the chain can have some missing values, you can declare default ones :

```php
$longuevue=new LongueVue('/articles/{id}/comments');
$longuevue->addDefaultValue('id','1');
// Will return array('id'=>'72')
$longuevue->match('/articles/72/comments');
// Will return array('id'=>'1')
$longuevue->match('/articles//comments');
```

License
-------

LongueVue is published under the [MIT license](http://dreamysource.mit-license.org).
