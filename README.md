# Contact Form 7 Validate Email

## Description

The Contact Form 7 Validate Email plugin enhances the functionality of Contact Form 7 by providing email validation. It checks if the entered email address exists in the WordPress user database before allowing form submission. If the email does not exist, it displays a custom error message to the user.

## Features

- Validates email addresses in Contact Form 7 submissions.
- Checks email existence against the WordPress user database.
- Displays custom error messages for invalid emails.
- Enhances user experience by preventing submissions with non-existent emails.

## Installation

1. Download the plugin ZIP file from [Plugin URI](https://github.com/fahdi/cf7-validate-wp-email).
2. Log in to your WordPress admin dashboard.
3. Navigate to the "Plugins" section.
4. Click "Add New" and then "Upload Plugin."
5. Choose the downloaded ZIP file and click "Install Now."
6. Activate the plugin.

## Usage

1. Make sure you have Contact Form 7 installed and configured on your WordPress site.
2. Identify the form you want to apply email validation to and take note of its form ID.
3. Open your theme's functions.php file or a custom plugin file.
4. Locate the `add_filter` hook for `wpcf7_validate` and modify the form ID condition in the `gravixar_email_exists` function to match your target form ID.
5. Customize the error message in the `gravixar_email_exists` function to suit your needs.
6. Save the changes.

Example:

```php
// Modify this condition to match your target form ID
if ( isset( $_POST['_wpcf7'] ) && $_POST['_wpcf7'] != 1315 ) // Only form id 166 will be validated.
```

7. Test your form. It should now validate email addresses against the WordPress user database.

## Configuration

No additional configuration is required beyond the steps mentioned in the "Usage" section. However, you can customize the error message to better fit your specific use case.

## Contributing

Contributions to this plugin are welcome. If you encounter issues or have suggestions for improvements, please open an issue or submit a pull request on the [GitHub repository](https://github.com/fahdi/cf7-validate-wp-email).

## License

This plugin is licensed under the [GNU General Public License 2.0 (GPL-2.0+)](http://www.gnu.org/licenses/gpl-2.0.txt).
