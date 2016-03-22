OpenX Text Banners With Simple Markup
=====================================

This plugin allows advertisers to easily manage text banners, without knowledge of tech details.
All they need, is to set banner links and texts like this:

    My [super ads] for only [[123$]]

That will be converted to text with 2 links of different styles.

Installation
============

1. Zip `plugins` and `www` dirs to `openXMarkedText.zip` (you can use included build.sh, make sure that you have done chmod +x on it)
2. Import archive `openXMarkedText.zip` via OpenX admin panel (`Plugins->install`)
3. Go to `openXMarkedText` settings, and tune styles/limits
  - (!) Don't use double quotes in attributes, only single one. OX has a bug, and " in config file crashes everything 
4. Create Text Zone, Users, Text campaigns.
5. That's all. For text campaigns users will be able to manage their banners:
  - start/stop
  - create new


TODO
----

1. Replace double quotes in settings values at submit 
2. Hide `Keywords` and `Comments` fields in banner add/edit form
