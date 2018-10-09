# HostPapa Deployer Recipes

## Installing

Define this repository in your Composer Schema

```YAML
"repositories": [
    {
        "type": "vcs",
        "url": "https://bitbucket.org/HostPapa/deployer-recipes"
    }
],
```

And add a version requirement to your dev dependencies
`"hostpapa/deployer-recipes": "^1.0.0"`

## Versioning

This repo follows [Semantic Versioning](https://semver.org/) for any changes. If
there are any backwards incompatible changes to recipes, we *must* declare a new
major version number.

## Usage

For usage of specific recipes, see the [Docs](docs) folder.
