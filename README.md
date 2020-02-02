# Livebuzz plugin for Craft CMS 3.x

This plugin will pull in Exhibitors from the the LiveBuzz API to a website, it also updates and removes Exhibitors.

The AutoSync feature will keep your site in sync with Livebuzz by updating every 15 minutes. There's also the option to sync manually with the click of a button.

The Livebuzz section in the Control Panel lets you browse all Exhibitors that have been imported into the site.

Twig extension allows for easy integration of Exhibitors into your templates.

NOTE: This is not an official Livebuzz plugin.

## Requirements

This plugin requires:

- Craft CMS 3.0.0-RC1 or later;

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

       cd /path/to/project

2. Then tell Composer to load the plugin:

       composer require burnthebook/craft3-livebuzz

3. In the Control Panel, go to Settings → Plugins and click the "Install" button for Livebuzz.

## Configuring Livebuzz

In the Control Panel, go to Settings → Livebuzz and enter the URL of your Livebuzz JSON API feed for Exhibitors - including the Campaign identifier.

Also in Settings → Livebuzz you should enter the Bearer used to authenticate your access to the Livebuzz API. 

Enable AutoSync or, for advanced users, create a cron job on your server. See [Syncing](#syncing).

If using AutoSync, note that the initial sync will happen 15 minutes after enabling AutoSync.

To trigger the sync manually, go in Control Panel → Livebuzz and click the "Sync Now" button.

## Exhibitors

    {% set exhibitors = craft().livebuzz.exhibitors().all() %}
    
    {% for exhibitor in exhibitors %}
      {{ exhibitor.companyName }}
    {% endfor %}
    
#### Parameters

`craft().livebuzz.exhibitors()` returns a query object that supports Craft's standard query parameters for ordering, sorting, limiting, as well as the following new parameters:

#### Properties

Exhibitor elements have the following properties:

- `id` - Craft's Exhibitor ID. Note this is different from Livebuzz's Exhibitor ID.
- `identifier` - Livebuzz's Exhibitor ID.
- `companyName` - Exhibitor's company name.
- `logo` - Exhibitor's logo as a relative path.
- `description` - Exhibitor's biography.
- `telephone` - Exhibitor's telephone number in international format.
- `emailAddress` - Exhibitor's email address.
- `websiteUrl` - Exhibitor's website URL.
- `stands` - An array of stand locations assigned to this Exhibitor with the following structure:

      [
        'E09', 'A1', 'B10'
      ]

- `addresses` - An array of addresses for this Exhibitor with the following structure:

      [ 
         { 
            "line_1": "First line",
            "line_2": "The second line",
            "line_3": "The third line",
            "city": "Derby",
            "county": "Derbyshire",
            "region": "West Midlands",
            "country": "GB"
         },
         { 
            "line_1": "1600 Amphitheatre Pkwy",
            "line_2": "Mountain View",
            "line_3": "94043",
            "city": "",
            "county": "",
            "region": "CA",
            "country": "USA"
         }
      ]

- `socialMediaChannels` - An array of social media channels for this Exhibitor with the following structure:

      [ 
         { 
            "type": "instagram",
            "url": "https://www.instagram.com/"
         },
         { 
            "type": "website",
            "url": "https://www.example.com/"
         },
         { 
            "type": "facebook",
            "url": "https://www.facebook.com/"
         }
      ]

## Syncing

The plugin comes with an AutoSync option which will automatically sync from the JSON API feed every 15 minutes or so via a pseudo cron job.

For more robust syncing, you can disable Auto Sync in the plugin settings and trigger it from a proper server cron job using the following Craft console command:

    craft livebuzz/feed/sync

---

Brought to you by [Burnthebook](https://www.burnthebook.co.uk)
