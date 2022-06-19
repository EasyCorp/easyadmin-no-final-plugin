EasyAdmin No Final Composer Plugin
==================================

EasyAdmin is [designed to not allow class inheritance][1] and that's why all
its classes use the `final` PHP keyword. This is troublesome for those who want
to extend EasyAdmin classes to add new features.

This project is a Composer plugin that removes the `final` PHP keyword from all
EasyAdmin classes, so you can extend all of them. It only modifies EasyAdmin
files located in the `vendor/` directory of your project. It doesn't change any
other file in your application.

Run the following command to install this Composer plugin in your projects:

```
$ composer require easycorp/easyadmin-no-final-plugin
```

When does this plugin update EasyAdmin classes?

* Just after installing this Composer plugin;
* Just after installing or updating any EasyAdmin version.

[1]: https://easycorp.github.io/blog/posts/the-road-to-easyadmin-3-no-more-inheritance
