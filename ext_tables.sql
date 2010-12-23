#
# Table structure for table 'tx_fdfxbeimage_dam_content_ref'
#
CREATE TABLE tx_fdfxbeimage_dam_content_ref (
  uid int(11) NOT NULL auto_increment,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  filename varchar(255) DEFAULT '' NOT NULL,
  convertparams tinytext,
  originalparams tinytext,
  
  PRIMARY KEY (uid),
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
