# FIZZ Object Logger Bundle

Allows logging entity changes to an entity for use in simple mutation logging systems. This is done differently than event logging (as seen in monolog) and should therefore be approached as a standalone component.

The logger registers a Doctrine event subscriber that listens to prePersist, preUpdate and preRemove and sends a unified model to a registered processor that is responsible for processing the event and saving the entity log instances. Don't worry -- there's a default processor that will get you started very easily.

## Installation

Install the package:
```sh
composer require fizz/object-logger-bundle
```

Register the bundle in `app/AppKernel.php`:
```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Fizz\ObjectLoggerBundle\FizzObjectLoggerBundle(),
    );
}
```

Configure entities you want to track in `app/config/config.yml`:
```yml
fizz_object_logger:
    ignored_fields:
        - modifyDate # optional - add global field names you don't want to track
    enabled_entities:
        - Acme\DemoBundle\AcmeDemoBundle\Entity\Blog
        - Acme\DemoBundle\AcmeDemoBundle\Entity\Comment
        - Acme\DemoBundle\AcmeDemoBundle\Entity\Like
    discriminator_mapping:
        Acme\DemoBundle\AcmeDemoBundle\Entity\Comment:
            # We want to log changes to the comment on blog level,
            # so we tell the logger to follow the 'blog' field association.
            - blog
        Acme\DemoBundle\AcmeDemoBundle\Entity\Like: # We can go as deep as we like.
            - comment
            - blog
```

## Usage

Once you're done configuring, you can start using the bundle. Create a controller to request the log:

```php
public function logAction(Blog $blog)
{
    $em = $this->getDoctrine()->getManager();
    $log = $em->getRepository('FizzObjectLoggerBundle:EntityLog')->findByEntity($blog);
    return $this->render('@AcmeDemo/Blog/log.html.twig', array(
        'log' => $log,
    );
}
```

And a view:
```twig
{% for item in log %}
	{{ item|render_entity_log }}<br />
	{# Of course, we can still access all properties in the item. Use dump(item) to see what you can do! #}
{% endfor %}
```

The render_entity_log filter is registered in a [Twig Extension](http://symfony.com/doc/current/templating/twig_extension.html). You can change its functionality by overriding the `fizz.object_logger.processor.event_listener.user_extra.class` parameter and setting it to a class that extends `Fizz\ObjectLoggerBundle\Twig\ObjectLoggerExtension`:
```php
public function renderEntityLog(EntityLog $log)
{
    if(!$log->getIsTranslated()) {
        return $log->getMessage();
    }
    return $this->translator->trans($log->getMessage(), $log->getTranslationParameters(), $log->getTranslationDomain());
}
```

If the default log processor doesn't suit your needs, you can create a custom one to do some very specific logging.

## Configuration reference
Default values:
```yml
fizz_object_logger:
    enable_default: true # Enable default processor.
    save_user: true # Add the username of the current user to the log's extra data.
    ignored_fields: [] # Globally ignored field names.
    enabled_entities: [] # Entities the default processor should process.
    discriminator_mapping: [] # Object reference mapping. See 'installation' for more details.
```

## License
This bundle is released under the GNU GPL 3.0 license. This means all modifications to the source code should be made publicly available. Easiest way of doing this would be to contribute or to fork the project in your own repository. Read LICENSE for more detailed information.
