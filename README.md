##### db class usage examples:
```php
// multi row table
db::pdo()->query('SELECT * FROM `categories`');
// or with bind
//db::pdo()->query('SELECT * FROM `categories` WHERE `id` = :id');
//$placeholders = array(':id' => '1');
//db::pdo()->bind($placeholders);
db::pdo()->execute();
if(db::pdo()->count() > 0):
    foreach(db::pdo()->result() as $category):
        echo $category->title, '<br/>';
        echo $category->description, '<br/>';
    endforeach;
endif;

db::pdo()->query('UPDATE `config` SET `site_name`="codemafia"');
db::pdo()->execute();

// single row table
db::pdo()->query('SELECT * FROM `config`');
db::pdo()->execute();
// then use our result function as an array instead:
$config = db::pdo()->result()[0];
echo $config->site_name, '<br/>';
echo $config->timezone, '<br/>';

db::pdo()->close();
```
##### pagination class usage example:
```php
$cid = 1;
$topics_per_page = 10;
$links_per_page = 5;
$append_to_links = 'cid='.$cid.'&amp;';
$query = 'SELECT * FROM `topics` WHERE `category_id` = :cid ORDER BY `reply_date` DESC';
$bind = array(':cid' => $cid);
pagination::init()->paginator($query, $bind, $topics_per_page, $links_per_page, $append_to_links);
	if(pagination::init()->count() > 0):
		foreach(pagination::init()->result() as $topic):
			echo sanitize($topic->title), '<br/>';
		endforeach;
	endif;
echo pagination::init()->links();
```