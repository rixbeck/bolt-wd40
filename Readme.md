# WD40 rust remover for [Bolt CMS](https://bolt.cm)

WD40 is an experimental project that aims Bolt CMS running on Nitro and removes some "PHP rust" from that fantastic CMS 
product. In our case "PHP rust" means that typical nature of PHP rebuilding all code on each HTTP request.
Rather we are letting him to build the code on first request and run as a long running worker in non/blocking manner.

These are all based on ReactPHP and PHP-PM on top of it.

What are we winning with this method beyond an incredible speed improvement? Enjoying a new aspect of coding and introducing
new technics of caching, optimizations and much more fun.

## How's further...

I recommend you first check what is asynchronous way of code execution and how it is implemented by 
[ReactPHP here](https://sergeyzhuk.me/reactphp-series) and [here](https://reactphp.org/).
  
## Then...

comes [PHP Process mnager](https://github.com/php-pm/php-pm) which *Is a Process Manager, Supercharger, and Load Balancer 
for PHP Applications* so we are going to use it as *PHP rust remover* for Bolt. If you read the most remarkable section 
will be the performance graph which promises a brutal speed up on requests.

The most important chapter is [how to prepare your PHP CGI environment](https://github.com/php-pm/php-pm/wiki/Use-without-Docker) 
you have to do just once. 

Because this stuff will run as a server, moreover on a Linux box I suppose you have all the usually requested tools like 
Git, Composer and so on. 

## And now...

If you want to live with instant reload of Bolt's config files under a running server in Linux environment you should install

```bash
apt install inotify-tools
```

or under Mac

```bash
# MacPorts
$ port install fswatch

# Homebrew
$ brew install fswatch
```

Checkout and prepare your WD40

```bash
git clone https://github.com/rixbeck/bolt-wd40.git
cd bolt-wd40
composer install
```
  
which will build up all the structure you need to run Bolt on nitro. To run the whole stuff you should start just the PM.
The process manager will sit here at

```bash
./vendor/php-pm/php-pm/bin/
```
  
so we can invoke our Bolt CMS dragster server as

```bash  
./vendor/php-pm/php-pm/bin/ppm start --config=./app/config/ppm/ppm.json
```

PHP-PM and its workers are easily set up for debugging as PM document describes. In this case just use 

```
./app/config/ppm/ppm-test.json
```

as process manager config. That's ensure for logging to console and only one worker for less confusion.
See other configuration options to customizing for your needs.

## Surprises?

I'm almost sure they're coming. Not just because this is a real experimental project but rather we may have things in Bolt
codebase possible aren't prepared to perform under this kind of circumstances despite how our codebase is designed carefully.
Although I can tell you I haven't stepped into any deep dirt on my box so just go on!

## Further more

While Bolt and Php-PM are both sophisticated and well designed and highly extendable I can imagine points where they
can be boosted. A few of are at Bolt can be

* **Bolt caching** - because our Bolt instances are persistent this way some of the cached portions of data can be put in plain PHP
variable space transparently with a PSR cache.
* **PDO prepared statements** - with actively using prepared statements we could win SQL statement preparation time which 
can be remarkable speed boost at DB intensive page loadings. 

...and at PHP-PM side

* **WebSocket channel** - for low latency communications to Bolt or push notifications with clients.
* **Remote admin statistics** - maybe, if we wants to know at client side what's going on at workers what about resources 
and load   
   
Something I'm really curious - and think you are all - is about its reliability. However PHP-PM promises worker management on 
memory leaks - which is PHP's most notorious attribution - we will see at work of course.

## Collaboration

I would be glad if this project would be a sort of "heads up" for other Bolters and join just for fun.

  
    