CREATE TABLE `oauth_access_tokens` (
  `access_token` VARCHAR(40) NOT NULL,
  `client_id`    VARCHAR(80) NOT NULL,
  `user_id`      VARCHAR(255)         DEFAULT NULL,
  `expires`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope`        VARCHAR(2000)        DEFAULT NULL,
  PRIMARY KEY (`access_token`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_authorization_codes` (
  `authorization_code` VARCHAR(40) NOT NULL,
  `client_id`          VARCHAR(80) NOT NULL,
  `user_id`            VARCHAR(255)         DEFAULT NULL,
  `redirect_uri`       VARCHAR(2000)        DEFAULT NULL,
  `expires`            TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope`              VARCHAR(2000)        DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_clients` (
  `client_id`     VARCHAR(80)   NOT NULL,
  `client_secret` VARCHAR(80)   NOT NULL,
  `redirect_uri`  VARCHAR(2000) NOT NULL,
  `grant_types`   VARCHAR(80)  DEFAULT NULL,
  `scope`         VARCHAR(100) DEFAULT NULL,
  `user_id`       VARCHAR(80)  DEFAULT NULL,
  PRIMARY KEY (`client_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_jwt` (
  `client_id`  VARCHAR(80) NOT NULL,
  `subject`    VARCHAR(80)   DEFAULT NULL,
  `public_key` VARCHAR(2000) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_refresh_tokens` (
  `refresh_token` VARCHAR(40) NOT NULL,
  `client_id`     VARCHAR(80) NOT NULL,
  `user_id`       VARCHAR(255)         DEFAULT NULL,
  `expires`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope`         VARCHAR(2000)        DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_scopes` (
  `scope`      TEXT,
  `is_default` TINYINT(1) DEFAULT NULL
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;

CREATE TABLE `oauth_users` (
  `username`   VARCHAR(255) NOT NULL,
  `password`   VARCHAR(2000) DEFAULT NULL,
  `first_name` VARCHAR(255)  DEFAULT NULL,
  `last_name`  VARCHAR(255)  DEFAULT NULL,
  PRIMARY KEY (`username`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = latin1;
