# Want to contribute?

First of all: ALL contributions are welcome! 

That being said, this package works with Docksal, a small layer upon docker-compose that will setup all the basics for
you, so you don't lose time setting everything up and installing all dependencies by hand.  
Here are some guidelines that may ease up development for you:

* Ensure Docker is installed
* Ensure Docksal is installed: [https://docksal.io/installation](https://docksal.io/installation)

* After all dependencies are installed, execute the following in project directory:

```bash
fin up
fin app-install
# Enjoy!
```

# Testing

## Unit tests

To run all unit tests:

```bash
fin phpunit
# Enjoy!
```

## Run code inspector

This project uses phpcs to validate the style guide (PSR-12).
To run the suite:
```bash
fin phpcs
```

## Creating documentation

Please don't commit the auto-generated documentation. To generate it:

```bash
fin generate-documentation
```

This will generate documentation in the `docs/` folder.
