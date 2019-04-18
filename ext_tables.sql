#
# Modifying pages table
#
CREATE TABLE pages (
    geo_region varchar(100) DEFAULT '' NOT NULL,
    geo_placename varchar(255) DEFAULT '' NOT NULL,
    geo_position_long varchar(255) DEFAULT '' NOT NULL,
    geo_position_lat varchar(255) DEFAULT '' NOT NULL,
);
