-- --------------------------------------------------------

--
-- Table `tl_iso_config`
--

CREATE TABLE `tl_iso_config` (
  `iso_livedocx_active` char(1) NOT NULL default '',
  `iso_livedocx_user` varchar(255) NOT NULL default '',
  `iso_livedocx_pass` varchar(255) NOT NULL default '',
  `iso_livedocx_template` varchar(255) NOT NULL default '',
  `iso_livedocx_outputformat` varchar(5) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;