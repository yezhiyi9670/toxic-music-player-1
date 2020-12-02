Programmable Rules
==================

Rules can be seen everywhere.

For example, you want your alarm app to go off every weekday at 06:20. It's a piece of cake. You just set an alarm at 06:20 on your phone and make it 'repeat' on 'Monday ~ Friday'. The pattern of repeating is a rule.

However sometimes, especially before or after long holidays, you'll need to work on Saturday or Sunday.

The day before that day...

Without thinking twice, you go to bed, thinking the alarm will go off tomorrow. But it doesn't.

On arriving at the office, you surprisingly find that it's already lunchtime.

Actually, you want your alarm to go off on every 'work day' instead of weekday. You may say it's still a piece of cake. ~~Google~~ Huawei has already allowed users to make alarms 'repeat' on 'work days'.

But what if you're a teacher of after-school lessons? Then you'll need to work on weekdays, weekends, but not during long holidays. It's no longer easily achievable using the Alarm app.

The requirements of users is always infinite. The best way of solving this problem is allowing user to take full control of the rules. This is why we need programmable rules.

0x00 Scheduled
--------------

Programmable rules are scheduled to be introduced in v201a, after The Flattening.

0x01 Make rules programmable!
-----------------------------

In the example above, we need a fully user-controllable rule, indicating whether the alarm should go off or not on a certain day.

Let's make it abstract. We need a user-defined function `should_go_off(date)` returning `true` or `false`, with a parameter indicating the certain day.

But what if you frequently travel to a weird country, where Wednesday and Thursday make up a weekend? Without letting the alarm know you geolocation, you need to edit the alarm on each travel. As all of us know, editing a function(or editing code) is expensive, especially on mobile devices.

Adding two integer parameters ranging from `[0,7)` (`should_go_off(date,weekend1,weekend2)`) perfectly solves this problem. You'll only need to edit the values of these two parameters instead of the function.

Here the parameter `date` is called a **varying parameter**, which is not defined explicitly but selected by the user and varies over time.

The parameters `weekend1` and `weekend2` are called **custom parameters**, which is explicitly defined and set by the user and does not vary over time unless the user edits the value.

0x02 How to do it?
------------------

### Defining Parameters

Here's a sample of parameter definition.

```plain
// |Selection      |Type and range   |Name         |User-friendly name

    varying(date)   date              date          "当天日期"
	custom          integer[0,6]      weekend1      "周末的第一天"
	custom          integer[0,6]      weekend2      "周末的第二天"
	custom          boolean           void_long_vac "跳过法定长假"
	...
```

Each parameter takes up a line in parameter definition. Empty lines are ignored. `//` or `#` stands for comments if it starts a line.

Indentation or trailing whitespaces are always supported, but redundant spaces are not supported.

Lines don't have to exactly align.

**Data Type**

Date: `date` (JavaScript Date object)

Integer: `integer`  
Integer with Range: `integer[<lower>,<upper>]` (range is only a metadata and does not validate the parameter)

Float: `float`
Float with Range: `float[<lower>,<upper>]` (range should only use decimal representation)

String: `string`

**Parameters**

```plain
varying(<id>) <type> <name> <desc>
```
```plain
custom <type> <name> <desc>
```

`<id>` is the id of the varying value, defined by the application.

`<type>` is necessary, but is ignored in varying parameters.

`<name>` should meet the requirements of variable name and shouldn't be longer than 128.

`<desc>` may have quotes. Quoted values will have escape sequences resolved.

### Function Body

After defining the parameters, you have to write the function body in JavaScript. You can refer to parameters using their names, and the function should return the value required by the application.

If the function produces uncaught errors, the application will handle those.

Breaking out of the function structure is possible here, but malicious code will not be successfully executed in the sandbox.


