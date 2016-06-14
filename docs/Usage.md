Usage
=====

To add a listener to specified controller action, use the following events 
configuration for your module:

The file `project/module/ExampleModule/config/events.config.php`:
```
return [
    'ExampleModule.Listener.Foo::doSomething' => [
        'ExampleModule.Controller.FooController@index',
        'ExampleModule.Listener.Foo',
        'doSomething',
        1500
    ],
];
```
In example above used the name of listener and method of listener to provide a 
unique configuration key:

- `ExampleModule.Listener.Foo::doSomething`

1. As first item of array must be specified the controller action in format
   `ControllerName@actionName`. The name of controller must be specified exactly
   as it was registered in `Controllers` service. The action name must be 
   specified without `Action` postfix.
2. As second item of array must be specified the full name of listener, exactly
   as it was registered.
3. As third item of array must be specified the name of listener method.
4. As fourth item of array must be specified the priority of handling. 
   Listeners with a priority greater than 1000 will be executed before the 
   controller action, less than 1000 - after a controller action.

To add a listener to any controller action:
The file `project/module/ExampleModule/config/events.config.php`:
```
use Es\Dispatcher\DispatchEvent;

return [
    'ExampleModule.Listener.Foo::doSomething' => [
        DispatchEvent::CLASS,
        'ExampleModule.Listener.Foo',
        'doSomething',
        1500
    ],
];
```

In example above used the name of listener and method of listener to provide a 
unique configuration key:

- `ExampleModule.Listener.Foo::doSomething`

1. As first item of array must be specified the class of `Es\Dispatcher\DispatchEvent`.
   This means that you want to listen any event of this class.
2. As second item of array must be specified the full name of listener, exactly
   as it was registered.
3. As third item of array must be specified the name of listener method.
4. As fourth item of array must be specified the priority of handling.
   Listeners with a priority greater than 1000 will be executed before the 
   controller action, less than 1000 - after a controller action.
