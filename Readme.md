The Mautic extension for TYPO3
===========
![Mautic](http://i.imgur.com/g56p37X.jpg "Mautic Open Source Marketing Automation together with the CMS power of TYPO3")

Welcome to the official TYPO3 extension for Mautic!

## Features
The Mautic TYPO3 extension has many features that allow you to integrate your marketing automation workflow in TYPO3.
### Dynamic Content Blocks
Ever wanted to serve different content to different users based on their Mautic segments? With this extension you will be able to set aside content in your TYPO3 website for specific Mautic segments. This way, you will be able to decide what content to serve to which people!
### Form Synchronization
With the Mautic extension for TYPO3 you can create your forms in the TYPO3 backend, and have all data collected in Mautic too! You no longer need to maintain two forms, the extension will automatically sync all forms you have marked as 'Mautic forms' with Mautic. You can then easily post form results to Mautic, while your form will always stay up-to-date with your TYPO3 edits.

### Form Contact Creation
Immediately create contacts through forms by assigning lead fields to form fields directly from the TYPO3 form engine!

### Mautic Form Content Element
If you wish to use Mautic forms directly, you can now add them with the Mautic Form content element that comes shipped with this extension.

### Tracking Script Integration
Integrate the Mautic tracking script into your frontend with one click of a button!

### OAuth support
All requests made by this extension are secured using OAuth1a. You can easily configure your API tokens in the extension manager of TYPO3.

## Installation
You can run the following command using Composer to install the extension:
```
composer require mautic/mautic-typo3
```
Then you must go into the backend of Mautic and create a pair of OAuth1a tokens. You can read how to correctly configure the API [here](https://www.mautic.org/blog/developer/how-to-use-the-mautic-rest-api/) under section "3. How to authorize your Mautic API application". After correctly configuring the API and creating a pair of OAuth1a tokens, you can enter them alongside of your Mautic URL in the extension configuration. As soon as these are saved, a button should pop up. This button will allow you to complete the OAuth authentication process and will retrieve the needed tokens. Once that is done, you are all set!

## Contributing
You can contribute by making a pull request to the master branch of this repository.

## Questions or Suggestions?
You can always open an issue in this repository if you find a bug or have a feature request. Next to that you can also come visit us on Slack via <https://mautic.slack.com> in the channel `#typo3-mautic`.