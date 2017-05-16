# Application architecture

In order for the application to be developed and tested in isolation from its infrastructural dependencies, and to
achieve a clean separation of concerns, the application architecture is based on
[Hexagonal Architecture](http://alistair.cockburn.us/Hexagonal+architecture).

Source code is divided into three layers:

1. The **domain** contains the business logic, encapsulated in entities and value objects. It protects its own state so
that it always contains valid data.
2. The **application** layer is a thin layer around the domain and contains entry points to interact with the domain,
such as commands and command handlers.
3. The **infrastructure** layer contains all infrastructural code, such as repository implementations, HTTP controllers,
etc. These can be seen as the adapters from ports & adapters.

These layers are represented as namespaces inside the `Surfnet\ServiceProviderDashboard` namespace.

This architecture allows for instance to test the domain- and application code without having to make calls to the
outside world (database, other applications, etc.) which significantly speeds up the execution of the test suite.

## Separation of read and write sides (CQRS)

The **domain** is only used for write operations, i.e. the handling of commands. For read operations, a simple read 
model will be used which exists of simple Doctrine entities with public properties. This read model will be part of the
infrastructure layer.

Since this read model reads its data from the same database as the data of the write model is written to (instead of 
"full" CQRS which consists of different databases), this seperation can be described as CQRS-lite.

Separating the read- and write sides of the application allows to deviate between them and gives more freedom to match
the data model to what is required for the user interface. It also saves some trouble that might occur when trying to 
force the use of entities and value objects on the read side.
