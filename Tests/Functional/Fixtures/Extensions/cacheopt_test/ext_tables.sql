#
# Table structure for table 'tx_cacheopttest_domain_model_record'
#
CREATE TABLE tx_cacheopttest_domain_model_record (
	uid INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	pid INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	tstamp INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	crdate INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	cruser_id INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	deleted TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL,

	title VARCHAR(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent(pid)
);
