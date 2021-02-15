# Directus Blog Plugin

**This README.md file should be modified to describe the features, installation, configuration, and general usage of the plugin.**

The **Directus Blog** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Blog generator for GRAV using directus plugin

## Installation

Installing the Directus Blog plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install directus-blog

This will install the Directus Blog plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/directus-blog`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `directus-blog`. You can find these files on [GitHub](https://github.com/erik-konrad/grav-plugin-directus-blog) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/directus-blog
	
> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/erik-konrad/grav-plugin-directus-blog/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/directus-blog/directus-blog.yaml` to `user/config/plugins/directus-blog.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
blog_table: directus_blog_table
blog_entrypoint: user/pages/08.blog
blog_filename: post.md
slug_field: zbr_slug
redirect_route: /nicht-verfuegbar
additional_params:
  filter:
    status:
      operator: _eq
      value: published
mapping:
  column_title: zbr_title
  column_date: zbr_date
  column_category: zbr_category
```
blog-table - the table with the blogposts

blog_entrypoint - the page path where the blog begins

blog_filename - the name of the generated file. default: post.md

slug_field - the field with the blogpost slug. This is used for the folder name of the blogpost

redirect_route - this is the redirect route if the blogpost is not found. The best way is to use the path to your 404 error site

additional_params - at the moment this is used for defining filters only. for a detailed description and a full param list look at https://docs.directus.io/reference/filter-rules/

mapping - here  is defined which field holds the necessary metadata in the blogpost table

Note that if you use the Admin Plugin, a file with your configuration named directus-blog.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

To start synchronising the blog, call the webhook yoursite.com/hook-prefix/refresh-blog

The hook-prefix is defined in the directus plugin configuration.

