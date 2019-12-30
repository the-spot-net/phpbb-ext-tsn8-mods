# The tsn phpBB Extension

## Purpose
This extension aims to suppliment the phpBB forum functionality with some custom features and styles developed over time for the-spot.net's community.

## Installation
Clone into phpBB/ext/tsn/tsn:

```shell script
git clone https://github.com/the-spot-net/phpbb-ext-tsn8-mods.git phpBB/ext/tsn/tsn
```
    
## Requirements
- This extension is intended to be used with the latest version of [phpBB 3.x](https://github.com/phpbb/phpbb/releases).
- This extension is not a standalone extension at this time, and currently requires the project [tsn8](https://github.com/the-spot-net/tsn8). In the near future, this repository will eventually evolve into a different/generic name.
- This external project makes modifications to the source code of phpBB where events and listeners have not yet been implemented for the purposes of doing everything in an extension.
- Additionally, certain modules in this extension are built with around certain database forum ID values and other identifiers that are part of the database values in the original tsn project.
- As such, while the source for the-spot.net is "Source Available," the project itself is not designed to be "Open Source."
    
## Original Extension Source
[phpbb 3.1 Acme Demo](https://github.com/nickvergessen/phpbb-ext-acme-demo.git)

## Extension License
[GPLv2](license.txt)
