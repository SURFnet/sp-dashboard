# How to add a new generator

1. make a copy of the JsonGenerator
2. get rid of all the stuff you don't need
3. ensure that it get's the tag you want via services.yml.  The example below uses a fictitious acl identifier:
```
tags:
    - { name: dashboard.json_generator, identifier: acl }
```
4. ensure it gets loaded via the JsonGeneratorStrategy.
