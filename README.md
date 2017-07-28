Brain Assistant Project
======================
Brain Assistant is a software complex which consists of the website and Android application. Its main goal is to give to user ability to save their task on a server and get reminders about them on all Android devices on which this app is installed

**Features:**
- Using Google Identity Token for identification and authorization of users on website and devices via Google account
- For this moment implemented two type of event for triggering notifications: Time and Location
- Convenient fast accessible panel with active tasks
- Ability extremely fast to add an image to task based on the text of task title using Android device.

**Website:**

http://www.brainas.net/

**Link to Android app on google play:**

https://play.google.com/store/apps/details?id=net.brainas.android.app&hl=en

Web Server Infrastructure
=========================

The Web Server of Brain Assistant Project is running on virtual machine that is working in a cloud hosting. I am using Ubuntu 16 as an operation system for server and Apache Web Server to handle HTTP requests.

MySQL used as a database management system for BA.


Unit & Functional testing
=========================

Most part of server's code covered with unit-tests. The classes were implemented with considering of Dependency Injection Principle that makes using of mock objects easily. I also use some functional tests for backend part that response for communication with outside Android devices. For organizing unit testing I am using <a href='http://codeception.com/'>Codeception</a>, <a href='https://phpunit.de/'>PHPUnit</a> and <a href='docs.mockery.io'>Mockery</a> frameworks.  
