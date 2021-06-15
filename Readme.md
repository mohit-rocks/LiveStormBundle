#Mautic LiveStorm Integration:
This plugin provides integration of [Livestorm](https://livestorm.co) with Mautic and provides features to import the webinar attendees and other details.

## Features
### Data and Import:
- Import the data from the [Livestorm](https://livestorm.co) via Livestorm API
   - Sync contact
   - Sync contact and related interactions count like upvotes, votes, messages etc.  
- Provide the field mapping on the Mautic, so incoming data can be mapped.
- Periodic syncronization of data.
- Import user interactions like upvotes, votes, messages count etc.
- Simple caching support to prevent multiple calls to API

### Points
- Integration with Mautic Points system so we can assign certain points to user when user joins webinar.

### Segments
- Based on data received from the API, segment users based on various criteria.
- At the moment, one filter is implement to segment users based on event attendance status.

## Setup
### Installation
- Install the module like other general Mautic plugins.
- Put it into `/plugins` directory.
- Reload all the plugins so it is visible.
- Click the `Livestorm` icon in the Mautic plugins page.
- Get the [Livestorm](https://livestorm.co) API key from [Livestorm Developer](https://developers.livestorm.co/) portal.
- Click on `LiveStorm` plugin icon, a modal will open with three tabs that provides necessary plugin configuration. 
- `Enabled/Auth`: In the first tab, provide `key`, `API url` and check the checkbox to enable plugin.
- `Features`: Enable what entities you want to sync. In our case, it is just `Contact` only.
- `Contact Field Mapping`: provide field mapping. Select appropriate field where data coming from API should be imported into Mautic Contact fields.
- Click on `Save & Close` once all the values are configured.

### Running sync Commands
As we are using `Integrations` bundle to build integration, we need to run following command to start the sync process:

```mautic mautic:integrations:sync livestorm --env=dev```

This command will sync all the data from Livestorm to API

### Points Assignments


### Segments


### Next steps:


Patches:

M3: https://github.com/mautic/mautic/pull/8739

M4: https://github.com/mautic/mautic/pull/8649
