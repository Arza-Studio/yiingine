# Yiingine
The Yiingine is a highly customizable content management system built on the [Yii 2.0 framework](https://github.com/yiisoft/yii2). Made for developpers by developpers, the Yiingine has been conceived with ease of customization and extensibility as its core principles, providing reliable and reusable administrative and client site management components so developpers can focus their energy on work that makes their applications unique.
* developped using proven technologies and techniques:
 * runs on Yii 2.0, building on its simplicity, speed and extendability, 
 * easy front-end development and/or theming with [Bootstrap](http://getbootstrap.com/);
 * simple and painless deployment and maintenance using [Composer](https://getcomposer.org/);
* fully functionnal administration interface using Yii 2.0's powerful GridView and a simple and intuitive layout;
* numerous components can be harnessed to create any type of front-end;
* core models can be customized at will to adapt them to the application's needs;
* virtually every view file or controller can be overriden by the client site to provide extra functionnality;
* fully internationalizable content (translations currently in English and French);
* speedy execution thanks to built-in caching;
* powerful user management with role based access control.

## Installation
* [get familiar with Yii 2.0](http://www.yiiframework.com/doc-2.0/guide-README.html);
* deploy the [yiingine-template](https://github.com/Arza-Studio/yiingine-template) project for a base from which to start developping your own application.

## Directory structure

```
assets/              web assets
base/                base components that are used throughout the project
behaviors/           core behaviors
caching/             components used by the Yiingine's caching facilities
config/              the Yiingine's configuration
console/             console tools
controllers/         default controllers for handling the administration interface and other web based functions
db/                  ActiveRecord base classes
grid/                components used to supplement Yii 2.0's GridView
i18n/                internationalization components
interfaces/          interfaces used in the Yiingine
libs/                libraries
messages/            translated messages
migrations/          migrations used in Yiingine deployment
models/              models used by the Yiingine
modules/             built-in modules
views/               views used by the Yiingine
web/                 components used in handling web requests
widgets/             widgets used by the Yiingine
```

## Requirements

Since the Yiingine is entirely built upon Yii 2.0, its requirements are that Yii, which for the moment are simply PHP 5.4.
