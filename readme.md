# ACT Admin
ACT admin is a plugin which provides facilities to edit various tables used by the ACT website:
+ recipients - table to drive the list of recipients in the contact us form admin
            to determine the recipient of the new signups email for a group.
+ wwareas - basic information for the map about wildlife warden areas, used to populate information on the map. 
+ ccareas - placeholder for input of information about carbon cutters for a proposed map.

This plugin adds a item ACT admin to the dashboard if the user has an appropriate role to
        manage at least one of these lists.
The ACT admin menu has an item for each list the user can administer

This is designed to work with the actionclimateteignbridge.org website.

## Roles
The following table shows the wordpress roles that are associated with each list
|List id|Role|
|-------|----|
|recipients|administrator|
|wwareas|administrator|
|ccareas|cc_admin|

cc_admin is a custom roles which should be defined via a suitable third-party plugin

## Recipients list
Recipients list is a simple csv file which is stored outside publicly accessible storage.
It has only 2 columns - name and email

### Recipients - name
This is the name that appears in the 'Choose topic' list in the contact us form on the website.
These names should align with the interests people sign up for on the mailchimp form under join.

### Recipients - email
This is the email address(es) of one or more persons to receive the message.
If more than one email address is specified, then these must be separated by a semi-colon (;)
When you select the Recipients List menu item, the list is displayed so that you 
 can edit it. When you have finished editing press Submit|

### Recipients - use with Contact Form 7
The recipients list is exported via a filter so that it can be used by the Contact Form 7 Plugin,
to do this enter a select of select* tag as follows on your form:
```
  [select* recipients]
```
Here recipients is both the name by which the list is known and the field name for reference on the mail tab of Contact Form 7.

## Wildlife Warden Areas
This list is driven by a predefined list of areas. To change details of an area:
1. Select the area from the list
2. The details of the area are shown with only the fields you can change editable
3. Make your changes
4. Press the submit button to write your changes back to the server.

### No. Wardens
This field should be set to the number of wardens
### Parish Text
This should be short descriptive text, typically provided by wardens for an area.

## Where do area boundaries come from?
Wildlife Warden Areas have been assigned based on a combination of parish boundaries and Newton Abbot ward boundaries.
A boundary map has been derived from data in 
[OS Boundary Line](https://www.ordnancesurvey.co.uk/products/boundary-line)
The codes that you will see are standard ONS codes, which are used to join the boundaries with other datasets.
# Configuration

## Location of the lists
For security reasons lists are stored outside the wordpress root (public_html), 
this means that ACT_admin needs to know where the lists are stored. This setting is stored in wp-config.php in the wordpress root,
the following line should be added to wp-config.php:
```php
    /* Path of lists maintained by ACT_admin */
    define( 'LISTS_DIR', '/home/customer/www/actionclimateteignbridge.org/jobs/');
```
It is suggested that this is added after ABSPATH is defined.

## Updating WildlifeWardenAreas map
This requires:
1. That ACT_update_ww_map.php should be put in the directory specified by LISTS_DIR
2. The WildlifeWardenArea.json should be in LISTS_DIR/../public_html/mapping/WW ,
3. if the default paths are assumed, this would be /home/customer/www/actionclimateteignbridge.org/public_html/mapping/WW
4. The group of WildlifeWardenArea.json should be set to www-data (assuming this is the web user), e.g. at the command line
```bash
    cd ~/www/actionclimateteignbridge.org/public_html/mapping/WW
    chgrp www-data WildlifeWardenArea.json
```
If the group is not changed, then ACT_update_ww_map.php will be unable to update this file.
5. If there is still a separate WW domain containing the map, then WildlifeWardenAreas.json should be linked to the above file.
    e.g. at the command line:
```bash
    cd ~/www/ww.actionclimateteignbridge.org/public_html/mapping/WW
    ln -s ~/www/actionclimateteignbridge.org/public_html/mapping/WW/WildlifeWardenAreas.json ./
```
This means that these are effectively the same file, so that updates applied to the copy in the main directly will also appear at the same time in the WW domain.
This step is no longer necessary now that ww. is part of the main domain.
NOTE: the steps described above require SSH terminal access to the actionclimateteignbridge.org siteground account.

# TODO
+ Ability for an administrator to specify the maintainer/maintainer of a list, who is not an administrator
# Troubleshooting</h1>
|Problem:|Developer console failed to load tooltips.js, namevalue.js, tableeditor.js|
|-------|---------------------------------------------------------------|
|Solution:|If the ACT_admin folder has been copied with FileZilla, these files are linked to another folder on sites.stringerhj.co.uk, these files need to be copied individually.|

|Problem:|Write failure in wwareas failed to update map error reported.|
|-------|---------------------------------------------------------------|
|Solution:|~/www/actionclimateteignbridge.org/public_html/mapping/WW/WildlifeWardenArea.json doesn't have write permissions|

|Problem:|Live WW Map not updated|
|-------|---------------------------------------------------------------|
|Solution:|/www/ww.actionclimateteignbridge.org/public_html/mapping/WW/WildlifeWardenArea.json is not
                    linked to ~/www/actionclimateteignbridge.org/public_html/mapping/WW/WildlifeWardenArea.json|

