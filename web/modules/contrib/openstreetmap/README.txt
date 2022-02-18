CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Recommended modules
* Installation
* Configuration
* Troubleshooting
* FAQ
* Maintainers

INTRODUCTION
------------

This module enables creating and syncing drupal nodes (nodes) based on
OpenStreetMap nodes and ways (OSM nodes). By default, it stores the ID and the
name of the OSM node, but any fields added to the Drupal node type present on
the synced entity will also be pulled in.

REQUIREMENTS
------------

* Geofield
https://www.drupal.org/project/geofield
* Overpass interpreter URL
It ships with a set of utilities for applying overpass queries and importing
the result of those queries as OSM nodes. You will need to enter an Overpass
interpreter URL
https://wiki.openstreetmap.org/wiki/Overpass_API#Public_Overpass_API_instances

RECOMMENDED MODULES
-------------------

To place these on a map, use any Drupal module capable of mapping data in the
Geodata field. I use the Leaflet Views submodule of
https://www.drupal.org/project/leaflet to great effect.

* Leaflet
https://www.drupal.org/project/leaflet

INSTALLATION
------------

Install the Easy OpenStreetMap module as you would normally install a
contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.

CONFIGURATION
-------------

1. Configure the OSM Settings
Navigate to Administration > Config > OSM Settings (/admin/config/osm)

- You will need to enter an Overpass interpreter URL.
https://wiki.openstreetmap.org/wiki/Overpass_API#Public_Overpass_API_instances

2. Create a new OSM Node type
Navigate to Administration > Structure > OSM Node Type

- Add OSM Node type
Create a new OSM Node type, like "Business"

- Add fields as desired, such as "wheelchair"
Anything that matches an OSM tag (with or without the `field_` prefix).
For instance, if you have a field named "field_bus" and the OSM Node
has a tag "bus: yes", then your Drupal node's field will have "yes"

MANUALLY ADDING NODES
---------------------
If you only have a handful of nodes that you want to keep in sync, you can
add them individually using their OSM IDs obtained at https://openstreetmap.org

1. Navigate to Administration > OSM Node List

2. Add OSM Node
Fill out the OSM node ID.
You can get the OSM node ID by using the online OSM editor.
You can also use ways, but check the "Is way" box.

3. Save OSM Node
On save, the OpenStreetMap module will get all matching tags as field values.

CONFIGURING OVERPASS QUERIES
----------------------------
The Overpass language https://wiki.openstreetmap.org/wiki/Overpass_API/Language_Guide
can be used for querying features in OpenStreetMap. This is the easiest
way to keep a group of nodes in sync with the OpenStreetMap module.
It requires the "OpenStreetMap Query Tools" submodule to be installed.
Use https://overpass-turbo.eu/ to test queries

1. Structure > Overpass Queries > Add Query (/admin/structure/osm_query/add)

2. Give the query a title ("All Bus Stops in Atlanta")

3. Fill out the query body using Overpass Syntax

4. Select the bundle (OSM Node Type) that you want nodes matching this
query to be saved into.

5. Save the query.

6. Go to the "Execute" tab of the newly created query. There you can preview
how many nodes the query will enter into the system.

7a. You can execute the query from this page, starting a batch operation or

7b. Visiting Administration > Content > OSM Node List, then clicking
"Sync All" (/admin/content/osm_node/sync)

NOTE: You can also run a query once by visiting
Administration > Content > Import OSM Nodes (admin/content/osm_node/import)

FAQ
---

To place these on a map, use any Drupal module capable of mapping data in the
Geodata field. I use https://www.drupal.org/project/leaflet to great effect.

* Enable the Leaflet and the Leaflet Views module
* Create a new view of OSM nodes
* Add the Geodata field
* Set up the Leaflet view type settings to get geodata from the Geodata field
* The map will now display all OSM nodes matching that query

MAINTAINERS
-----------

This module is under active development. Please contact tyler@fivepaths.com with
questions on use. Feature requests will not be considered until the first stable
release.
- https://www.drupal.org/u/tbcs
