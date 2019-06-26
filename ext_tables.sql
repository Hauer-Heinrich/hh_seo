#
# Modifying pages table
#
CREATE TABLE pages (
    html_head text DEFAULT '' NOT NULL,
    html_body_top text DEFAULT '' NOT NULL,
    html_body_bottom text DEFAULT '' NOT NULL,
    geo_region varchar(100) DEFAULT '' NOT NULL,
    geo_placename varchar(255) DEFAULT '' NOT NULL,
    geo_position_long varchar(255) DEFAULT '' NOT NULL,
    geo_position_lat varchar(255) DEFAULT '' NOT NULL,
);
