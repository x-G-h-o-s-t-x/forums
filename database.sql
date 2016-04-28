SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `forum_id`, `title`, `description`) VALUES
(1, 1, 'General Discussions', 'General web/wapmasters discussions forum.'),
(2, 1, 'Coding Forum', 'HTML, WML, CSS, mySQL, PHP, ASP, CGI, JavaScript coding discussions and help.'),
(3, 1, 'Scripts Forum', 'Free HTML, WML, CSS, mySQL, PHP scripts for websites and mobile wapsites.'),
(4, 1, 'Domains, Hosting, Servers', 'For general discussion about domain names, hosting, cPanel, Plesk etc.'),
(5, 2, 'Server Management', 'Linux commands. Server configuration, optimization and install instructions.'),
(6, 2, 'Site/Script testing/error fixing', 'Here you can ask for help fixing some script errors, or just ask for help testing your site with different mobile devices.'),
(7, 2, 'Design, Templates, Graphics', 'Logos, graphic tools, banners, anything to do with web design and graphics, CSS - styles, templates etc.'),
(8, 3, 'Marketplace', 'here you can sell your domains, scripts, modifications, design graphics or offer some paid services like script installation etc. You can also place here your requests if you want to buy some scripts.'),
(9, 3, 'Freelance Jobs & Projects', 'looking for a coder to develop a website or just to make changes, upgrades, improvements to your site, post your job offers here.'),
(10, 3, 'Sites & Links', 'Show us your sites.');

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `site_name` text NOT NULL,
  `site_logo` text NOT NULL,
  `announcement` varchar(255) NOT NULL,
  `timezone` text NOT NULL,
  `debug` varchar(5) NOT NULL,
  `seo` text NOT NULL,
  `topics_per_page` int(11) NOT NULL,
  `posts_per_page` int(11) NOT NULL,
  `auto_delete_shouts` text NOT NULL,
  `show_shouts_to_guests` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`site_name`, `site_logo`, `announcement`, `timezone`, `debug`, `seo`, `topics_per_page`, `posts_per_page`, `auto_delete_shouts`, `show_shouts_to_guests`) VALUES
('codemafia', 'core/images/codemafia.png', 'this script is currently being built from the ground up. stay tuned for up and coming news.', 'Europe/London', 'true', 'true', 10, 10, 'false', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `forums`
--

INSERT INTO `forums` (`id`, `title`) VALUES
(1, 'Main Forum'),
(2, 'Test Forum'),
(3, 'Another Test Forum');

-- --------------------------------------------------------

--
-- Table structure for table `online`
--

CREATE TABLE IF NOT EXISTS `online` (
  `uid` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `page` varchar(200) NOT NULL,
  `ip` varchar(30) NOT NULL,
  `member` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `category_id`, `topic_id`, `creator`, `content`, `date`) VALUES
(1, 1, 1, 1, 'just a test for when i mess about', '2016-01-15 21:59:19'),
(2, 1, 1, 1, '[php]\n/** bbcodes */\nfunction bbcodes($data) {\n    $data = preg_replace_callback(''/\\[php\\](.*?)\\[\\/php\\]/msi'', create_function(''$matches'', ''{\n        $matches[1] = html_entity_decode($matches[1], ENT_QUOTES, \\''UTF-8\\'');\n            if(preg_match(\\''/^(<\\?|<\\?php)\\s*?.*?\\s*?^\\?>\\s*?$/msi\\'', $matches[1], $new_matches)):\n                $matches[1] = highlight_string($new_matches[0], 1);\n            else:\n                $matches[1] = highlight_string(\\''<?php\\''.$matches[1], 1);\n                $matches[1] = preg_replace(\\''/&lt;\\?php\\s*?<br \\/>(.*?<\\/span>)/msi\\'', \\''$1\\'', $matches[1]);\n            endif;\n        return \\''<div class="codebox">\\''.$matches[1].\\''</div>\\'';\n    }'') , $data);\n    $bbcode = array(\n        ''\\[url\\](.*?)\\[\\/url\\]'' => ''<a href="$1" target="_blank">$1</a>'',\n        ''\\[url\\=(.*?)\\](.*?)\\[\\/url\\]'' => ''<a href="$1" target="_BLANK">$2</a>'',\n        ''\\[color\\=(.*?)\\](.*?)\\[\\/color\\]'' => ''<font color="$1">$2</font>'',\n        ''\\[h1\\](.*?)\\[\\/h1\\]'' => ''<h1>$1</h1>'',\n        ''\\[h2\\](.*?)\\[\\/h2\\]'' => ''<h2>$1</h2>'',\n        ''\\[h3\\](.*?)\\[\\/h3\\]'' => ''<h3>$1</h3>'',\n        ''\\[h4\\](.*?)\\[\\/h4\\]'' => ''<h4>$1</h4>'',\n        ''\\[h5\\](.*?)\\[\\/h5\\]'' => ''<h5>$1</h5>'',\n        ''\\[h6\\](.*?)\\[\\/h6\\]'' => ''<h6>$1</h6>'',\n        ''\\[small\\](.*?)\\[\\/small\\]'' => ''<small>$1</small>'',\n        ''\\[b\\](.*?)\\[\\/b\\]'' => ''<b>$1</b>'',\n        ''\\[i\\](.*?)\\[\\/i\\]'' => ''<i>$1</i>'',\n        ''\\[u\\](.*?)\\[\\/u\\]'' => ''<u>$1</u>''\n    );\n        foreach($bbcode as $from => $to):\n            $data = preg_replace(''/''.$from.''/msi'', $to, $data);\n        endforeach;\n    return $data;\n}\n[/php]', '2016-01-15 22:17:34'),
(3, 1, 1, 1, '[php]\n    public static function seo($data) {\n        if(preg_match(''/true/i'', self::config()->seo)):\n            if(preg_match(''/topic.php\\?cid=(\\w+)&amp;tid=(\\w+)&amp;page=(\\w+)/i'', $data, $matches)):\n                $title = null;\n                $links = db::mysqli()->query(''SELECT * FROM `topics` WHERE `id`="''.db::mysqli()->sanitize($matches[2]).''" LIMIT 1'');\n                    if($links->count() > 0):\n                        foreach($links->results() as $link):\n                            $title = str_replace('' '', ''-'', $link->title);\n                        endforeach;\n                    endif;\n                $data = preg_replace(''/topic.php\\?cid=(\\w+)&amp;tid=(\\w+)&amp;page=(\\w+)/i'', ''c$1/''.$title.''-$2/index$3.html'', $data);\n                return $data;\n            endif;\n        else:\n            return $data;\n        endif;\n        return $data;\n    }\n[/php]', '2016-01-15 22:58:25'),
(4, 1, 2, 1, 'testing', '2016-01-15 23:39:24'),
(5, 1, 1, 1, '[php]\r\n    /** seo function for forum topics */\r\n    public static function seo($data) {\r\n        if(preg_match(''/true/i'', self::config()->seo)):\r\n            if(preg_match(''/topic.php\\?cid=(\\w+)&amp;tid=(\\w+)&amp;page=(\\w+)/i'', $data, $matches)):\r\n                $title = null;\r\n                $links = db::mysqli()->query(''SELECT * FROM `topics` WHERE `id`="''.db::mysqli()->sanitize($matches[2]).''" LIMIT 1'');\r\n                    if($links->count() > 0):\r\n                        foreach($links->results() as $link):\r\n                            $title = str_replace('' '', ''-'', str_replace(array(''`'', ''&quot;''), '''', sanitize($link->title)));\r\n                        endforeach;\r\n                    endif;\r\n                $data = preg_replace(''/topic.php\\?cid=(\\w+)&amp;tid=(\\w+)&amp;page=(\\w+)/i'', ''c$1/''.$title.''-$2/index$3.html'', $data);\r\n                return $data;\r\n            endif;\r\n        else:\r\n            return $data;\r\n        endif;\r\n    }\r\n[/php]', '2016-01-16 22:10:32'),
(6, 1, 1, 6, 'I wish i could code like you your reqlly awesome', '2016-03-20 21:24:24'),
(7, 1, 1, 1, 'i learn by doing bud. i just think like:\r\n\r\nhey i wonder if i can build a forum also?\r\ncant be that hard right??\r\nwhat do i need to know?\r\noh that''s right php, css, html. cool.\r\nthen i''m like. oh this sidebar panel seems interesting, wonder how it works.?\r\n{googles: php recent posts examples}\r\noh cool. thats not hard, maybe i can use these idea''s and come up with my own version.?\r\nawesome my sidebar is functioning awesomely. its even better now i saw other ideas\r\n(right/wrong ways) of doing it.\r\n\r\nit would also help if i had people to talk code with. but these days that doesn''t really happy.\r\n\r\nthank you.', '2016-03-21 14:45:52'),
(9, 1, 1, 1, 'happy = happen* lol', '2016-03-21 14:48:45'),
(11, 1, 3, 1, 'quicker test', '2016-04-25 01:30:30');

-- --------------------------------------------------------

--
-- Table structure for table `private`
--

CREATE TABLE IF NOT EXISTS `private` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` longtext NOT NULL,
  `date` datetime NOT NULL,
  `read` varchar(4) NOT NULL,
  `delete_to` varchar(4) NOT NULL,
  `delete_from` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ranks`
--

CREATE TABLE IF NOT EXISTS `ranks` (
  `id` int(2) NOT NULL,
  `rank` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ranks`
--

INSERT INTO `ranks` (`id`, `rank`) VALUES
(0, 'Member'),
(1, 'Head Admin'),
(2, 'Administrator'),
(3, 'Moderator');

-- --------------------------------------------------------

--
-- Table structure for table `shouts`
--

CREATE TABLE IF NOT EXISTS `shouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `shout` varchar(300) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `uid` int(11) NOT NULL,
  `rank` int(2) NOT NULL,
  `forums` varchar(5) NOT NULL,
  `categories` varchar(5) NOT NULL,
  `topics` varchar(5) NOT NULL,
  `posts` varchar(5) NOT NULL,
  `shouts` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `username`, `uid`, `rank`, `forums`, `categories`, `topics`, `posts`, `shouts`) VALUES
(1, 'Ghost', 1, 1, 'true', 'true', 'true', 'true', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `creator` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `reply_date` datetime NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `category_id`, `title`, `creator`, `date`, `reply_date`, `views`) VALUES
(1, 1, 'test topic', 1, '2016-01-15 21:59:19', '2016-04-25 01:18:24', 329),
(2, 1, ''' Where `1`="1"', 1, '2016-01-15 23:39:24', '2016-01-15 23:39:24', 108),
(3, 1, 'a quick test', 1, '2016-04-25 01:30:29', '2016-04-25 01:30:29', 6);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `authentication` varchar(255) NOT NULL,
  `authkey` text NOT NULL,
  `rank` int(2) NOT NULL DEFAULT '0',
  `avatar` varchar(255) NOT NULL DEFAULT 'core/images/avatars/default.gif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `authentication`, `authkey`, `rank`, `avatar`) VALUES
(1, 'Ghost', '$2y$10$8jyXARl4DEyhKb733NAoROO8SnbsUCgMpNqxdJ6/2vxlmXeGeHkEW', '$2y$10$tZtpOT.Su5ew5nfGXRFpveATb3PKb8g5fSv5uyaSrZfVdlL74Z06q', 'a71909d424edd186fdf167ebde439bca', 1, 'core/images/avatars/default.gif'),
(2, '<script>', '$2y$10$M5rDCQ.sJC0alPke34P8h.in4yrQ.ulMPY4ApiqOKk8rmtuQp0JTW', '$2y$10$CxeHou6Knwg.aeO5ktz7hOMWBubZ2HZjARO3b5UnIwQlTC0WeBv6O', '0873f68b6f30442f937ac556b4900729', 0, 'core/images/avatars/default.gif'),
(3, 'test', '$2y$10$3BnlTDeHxwznYQ/QbbTKK.doFahNEbNIMTVfVbvWIgnxhMiELDVRq', '$2y$10$dC0oZ5l60ckc8PrjFH5BeObcQw3SJ1n7XLolIvZoAGZqo9cNRR2jO', '9e42309d9eb38ac0729bd5e10ad1f19c', 0, 'core/images/avatars/default.gif'),
(4, 'siam', '$2y$10$UI2x5qZBlfiuyqKJcLR6p.AbXBcKmU5jd/N3w1gVf7bkCySg9wIeW', '$2y$10$Qe6YxlzB6fdxdqu1RD4IxOqPfDDAVm.eQud5cAsHNX4HGoqF5sz6y', '6b71e8d76840d409313fc3acb589477c', 0, 'core/images/avatars/default.gif'),
(5, 'rider', '$2y$10$J/Ks5os1q3p0hbe0AIwgt.xbfhOZotMKYU2eree7MQStqC.pIxEYC', '$2y$10$IiRIOHorCQnyxTglVoWiD.MaEc3kWtA29QvM85SczFgRohAmnkJxW', '403e7bee98a2e31afe2142ada9af2600', 0, 'core/images/avatars/default.gif'),
(6, 'test2', '$2y$10$j8j7fzJyMplpu7kSafqWZO8B818IgFzZPAVur5etLDUw.I.lfRYGq', '$2y$10$5Cfo7EiH57OBvYMM1aqqZ.P.cjxLdomcrkLuvnM7lJyzxahMC8aFK', '75af2dedb97f74206d29c8c714bab0bb', 0, 'core/images/avatars/default.gif'),
(7, 'skp99', '$2y$10$90leilcNOl7p3rDgB/KglOY/1lKQNNoRPHByldGwXeCOQDv40ELlq', '$2y$10$MmfEewYR1mGHQTrbeaw/n.fl8pZI9gFzTsBXYTr1Ss05WbHSqa9Qu', '79774fe0b9d91f2d7367e350fa3a468f', 0, 'core/images/avatars/default.gif');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
