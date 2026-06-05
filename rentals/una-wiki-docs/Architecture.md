**Core** consists of some basic files like utilities, base classes, interfaces. 
**Objects** are helper classes which provide high level interface for some functionality.
**Plugins** are 3rd-party libraries.
**Modules** are separate, complete, independent piece of code which can be added or removed at any time.


![UNA Architecture Diagram](images/architecture-diagram.png)

**M** - Model, `*Db` classes

**C** - Controller, `*Module` classes

**V** - View, `*Templ*` classes and files in `/template/` folder.