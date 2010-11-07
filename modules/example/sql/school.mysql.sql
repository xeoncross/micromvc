--
-- MySQL 5.1.47
-- Sun, 07 Nov 2010 03:01:42 +0000
--

CREATE TABLE `car` (
   `id` int(11) unsigned not null auto_increment,
   `name` varchar(30) not null,
   `student_id` int(10) unsigned not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


CREATE TABLE `club` (
   `id` int(11) unsigned not null auto_increment,
   `name` varchar(30) not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


CREATE TABLE `dorm` (
   `id` int(11) unsigned not null auto_increment,
   `name` varchar(30) not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


CREATE TABLE `membership` (
   `id` int(10) unsigned not null auto_increment,
   `club_id` int(10) unsigned not null,
   `student_id` int(10) unsigned not null,
   `joined_on` datetime not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


CREATE TABLE `student` (
   `id` int(10) unsigned not null auto_increment,
   `name` varchar(30) not null,
   `dorm_id` int(10) unsigned not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;