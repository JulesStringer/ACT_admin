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
This functionality is now handled by ACT_maps.

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

# Fixed in this version
+ This plugin was causing 404 not found errors in the admin pages of other plugs, this leak has been fixed.
# TODO
+ Ability for an administrator to specify the maintainer/maintainer of a list, who is not an administrator
+ Add table_editor folder clone in the same manner ACT_maps
# Troubleshooting
|Problem:|Developer console failed to load tooltips.js, namevalue.js, tableeditor.js|
|-------|---------------------------------------------------------------|
|Solution:|If the ACT_admin folder has been copied with FileZilla, these files are linked to another folder on sites.stringerhj.co.uk, these files need to be copied individually. This needs a better solution, because the link references get checked into git and github, these should be in some kind of common library which has its own repository and versioned delivery mechanism|


