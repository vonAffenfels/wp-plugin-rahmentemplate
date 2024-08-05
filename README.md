# wp-plugin-rahmentemplate

## Overview
The WP Rahmentemplate Plugin allows users to add, manage, and apply templates within the WordPress backend. 
This plugin provides a simple and efficient way to handle content templates, ensuring consistency and ease of use across your site.

### Features
- Add Templates in WP Backend: 
Easily add new templates with URL, name, and text replacement fields.
- Fetch Templates with Guzzle: 
Retrieve templates from external URLs using the Guzzle HTTP client.
- Template Caching: 
Templates are cached to improve performance and reduce the number of external requests.
- Clear Cache: 
Clear the cache to fetch the latest version of templates.
- Use Templates in Posts: 
Apply templates to posts, ensuring a consistent structure and style.
- Protected Templates: 
Templates that are in use cannot be deleted, preventing accidental loss of important content.
- Default Template: 
Set a default template for new posts to streamline content creation.

## Installation

### Composer
1. Add this to the repositories in your composer.json
```json
     {
      "type": "git",
      "url": "git@github.com:vonAffenfels/wp-plugin-rahmentemplate.git"
     }
```
2. Require it by adding it to your composer.json
```json
    "require": {
        "va/wp-plugin-rahmentemplate": "1.0.0",
    }
```
3. Execute "composer update"

## Usage
### Adding a Template
Go to the WP Template Manager section in the WordPress admin dashboard.
Click on "Add New Template".
Fill in the required fields:
URL: The URL to fetch the template content.
Name: The name of the template.
Text to Replace: Specify the placeholder text that will be replaced in the template. (Standard: 'CONTENT')

### Fetching Templates
Templates are automatically fetched using the Guzzle HTTP client when you add a new template with a URL. The content is cached for faster access.

### Managing Cache
Clear Cache: Go to the Rahmentemplate Settings Page and click on "Details", Mark "Cache leeren" and click on "Markierte Caches leeren" to remove all marked cached templates. 
This forces the plugin to fetch the latest templates from their URLs.

### Applying Templates to Posts
In the post editor, you will find a "Select Template" dropdown in the sidebar.
Choose the desired template to apply it to your post. The content of the template will replace the placeholders specified.

### Default Template
To set a default template, go to the Rahmentemplate Settings Page.
Select a template from the dropdown to be used as the default for all new posts.

### Template Protection
Templates that are currently in use cannot be deleted. This is to ensure that content relying on these templates remains intact.