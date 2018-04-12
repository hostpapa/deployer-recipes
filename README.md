# HostPapa Deployer Recipes

This repository houses common Deployer recipes used by all projects.

With this being a private repository and due to some quirks in how Bitbucket
handles SSH keys, it needs to be set up in a special way in the Composer
configuration file.

## Installation

Add the following line to the repositories section of the `composer.json` of the
project you're adding notifications to.

```json

"repositories": [
    { "type": "vcs", "url": "https://bitbucket.org/HostPapa/deployer-recipes.git" }
],
```

Add the following to the development requirements

```json
"require-dev": {
    "deployer/deployer": "^6.1",
    "hostpapa/deployer-recipes": "dev-master"
},
```

## Usage

For usage of specific recipes, see the [Docs](docs) folder.
