<!doctype html>
<meta charset="utf-8">
<title>Decibel</title>
<script src="js/jquery-latest.min.js"></script>
<script src="js/jquery.toc.min.js"></script>
<style>
  body{font-family:Helvetica; max-width: 760px; margin:0 auto;}
  h1,h2{font-size:40px; font-weight:300; color:rgba(120,120,155,0.9);  margin:25px 0 0 15px;}
  h2{font-size:25px; margin: 0 0 15px 15px; color:rgba(155,120,150,0.9);}
  h3,h4{font-size:30px; font-weight:300; margin:15px; margin-bottom:7px;  }
  h4{font-size:20px; }
  pre{color:rgba(20,20,20,0.7); padding:0 20px; border: 1px solid rgba(20,20,20,0.2); margin: 20px auto; width: 85%;}
  pre.php {background-color:rgba(220,220,255,0.4)}
  pre:not(.php) {background-color:rgba(210,230,200,0.6)}
  code.query{display:block; color:rgba(20,20,20,0.7); background-color:rgba(255,220,250,0.4);
             padding:0 20px; border: 1px solid rgba(20,20,20,0.2); margin: 20px auto; width: 85%;}
  li{list-style: none;} 
  li:before{content:"· "; color: gray;}
  a{color:rgba(120,120,155,0.9);}
  nav{font-size:0.8em; position:fixed; top: 20px; left:-20px; max-width:225px;}
  nav .toc-active{background-color:rgba(210,230,200,0.6);}
</style>
<body>
<nav></nav>
  <h1>Decibel ))</h1>
  <h2>A cool way to manage your database</h2>
  
  Decibel is a PHP class using the MySQLi module to interact with your MySQL database.<br>
  I will show you step by step how to use it just right now.

<h3>Include the class</h3>

<pre class="php">
  <code>
include_once('./decibel.v1.php');
  </code>
</pre>

<h3>Create a new connection</h3> 

For this php file to work properly, edit this the connection using a
mysql user with the privilegies of creating, modifying and deleting
tables in the <code>test</code> database:
<pre class="php">
  <code>
$g=new decibel('127.0.0.1','root','password','test');
  </code>
</pre>

<?php
/// Edit here
include_once('./decibel.v1-ross.php');
$g=new decibel('127.0.0.1','root','password','test');
?>

<h3>Create a new table</h3> 

Now that a new instance has been created, it is possible to use a
<code>decibel</code> method to create a table, providing a table name
(<code>"decibeltest"</code>) and an array of column definitions. In this case we
will set two columns: <code>"id"</code> (integer, that will be also the primary key) and
<code>"name"</code> (string):
<pre class="php">
  <code>
$g->create("decibeltest",["id int auto_increment primary key","name varchar(20)"]);
$g->query();
  </code>
</pre>
The <code class="method">create()</code> method, along with almost all
the other that we will see, will write a mysql query ready to be
executed, while the <code class="method">query()</code> method will
actually execute it.  Let's print out the query that we executed and
let's show as well what we've done in json format. If you created the connection
correctly, you should see two boxes:

<?php 
$g->drop("decibeltest")->query();
$g->drop("decibelnice")->query(); 
$g->create("decibeltest",array("id int auto_increment primary key","name varchar(20)")); 
/// Executing the query 
$g->query(); 
tab($g);
?>

Uhm, right, we created an empty table. What about fill it with some value?

<h3>Insert some value in the table</h3> 

I will introduce an array of my cousins using
the <code class="method">insert()</code> method.  Every cousin is
itself an array containing an <code>id</code> and a <code>name</code>.
Some <code>id</code> is missing (<code>null</code>), but I created my
table with the <code>AUTO_INCREMENT</code> flag, so it's ok.

<pre class="php">
  <code>
$cousins=[[1,"marco"],[2,"claudio"],[3,"davide"],[null,"luca"],[5,"matteo"]];
$g->insert("decibeltest", ["id","name"], $cousins)
  ->query();
  </code>
</pre>
Look, you can chain the <code class="method">query()</code> method to
the previous ones, as in jQuery!<br>  Ok, let's check again the query
and the table:

<?php 
$cousins=array(array(1,"marco"),array(2,"claudio"),array(3,"davide"),array(null,"luca"),array(5,"matteo"));
$g->insert("decibeltest", array("id","name"), $cousins)
->query();
tab($g);
?>

<h3>Replace a value</h3> 

Now you have understood the logic.  I
use <code class="method">replace()</code> to change my name with
another of my cousins:
<pre class="php">
  <code>
$g->replace(null, [3,"andrea"])->query();
  </code>
</pre>
Why <code>null</code> on first argument? Because the first argument is
the table name, and if it is not specified <code>decibel</code> assume that it is
the last one setted, i.e. <code>"decibeltest"</code>.

<?php
$g->replace(null, array(3,"andrea"))->query();
tab($g);

?>

<h3>Get the data</h3> 

The <code class="method">query()</code> method just retrieves the
results, but does not create any array or object.  Why? because we may
want data in different formats for different
needs.  <code>decibel</code> implements up to now several ways to fill
the results into an array. 

<h4>Populate</h4> 

One of them is
the <code class="method">populate()</code> method: it creates an
associative array:
<pre class="php">
  <code>
$g->select(ALL,"decibeltest")
  ->limit(3)
  ->query()
  ->populate();
  </code>
</pre>

<?php
$g->select(ALL,"decibeltest")->limit(3)->query();
$g->populate();
echo '<code class="query">'.$g->query.'</code>';
?>

Now the associative array is ready, and we can use
the <code class="method">inspect()</code> method to look at its
content within handy <code>&lt;pre&gt;&lt;code&gt;</code> tags.  It's
the method I used until now to show you the various steps of the
<code>"decibeltest"</code> table:
<pre class="php">
  <code>
$g->inspect();
  </code>
</pre>
The results is again

<?php
$g->inspect();
?>

<h4>Compact</h4> 

Another way to fill the array is the <code class="method">compact()</code> method: 
<pre class="php">
  <code>
$g->select(ALL,"decibeltest")
  ->query()
  ->compact()
  ->inspect();
  </code>
</pre>
which creates one array per column. This method is not reccomanded by
the StackOverflow guys, but may be useful to build quick lists.
 
<?php
$g->compact()->inspect();
?>

<h4>Keys and Values</h4> 

...Or we can create array with only <code class="method">keys()</code>
or only <code class="method">values()</code>: instead of inspecting,
let's show the values as plain json using
the <code class="method">show()</code> method, and let's write the
keys in the javascript console.log of your browser using the <code class="method">log()</code> method:

<pre class="php">
  <code>
$g->values() // only the values
  ->show();  // plain json

$g->keys()  // only the keys
  ->log();  // look in the console!
  </code>
</pre>
This is the result of the <code class="method">values()</code> shown
with the <code class="method">show()</code> method: plain json not
wrapped into html tags<br>

<?php
$g->values()->show();
$g->keys()->log();
?>

<br>
And the <code class="method">keys()</code> method? Look in you javascript console!
<p>Now we are ready for a showreel of handful methods:

<h3>Delete some line</h3>
...with the <code class="method">delete()</code> method:
<pre class="php">
  <code>
$g->delete()->where("name='andrea'")->query();
  </code>
</pre>

<?php
$g->delete()->where("name='andrea'")->query();
tab($g);
?>

<h3>Add a column</h3>
...with the <code class="method">addcol()</code> method:
<pre class="php">
  <code>
$g->addcol(null,["familyname varchar(30)"])->query();
  </code>
</pre>

<?php
$g->addcol(null,array("familyname varchar(30)"))->query();
tab($g);
?>

<h3>Update a line</h3>
...with the <code class="method">update()</code> method:
<pre class="php">
  <code>
$g->update(null,["id='10'","familyname='ricci'"])->where("name='davide'")->query();
  </code>
</pre>

<?php
$g->update(null,array("id='10'","familyname='ricci'"))->where("name='davide'")->query();
tab($g);
?>

<h3>Rename a column</h3>
...with the <code class="method">renamecol()</code> method:
<pre class="php">
  <code>
$g->renamecol(null,"familyname","lastname","varchar(20)")->query();
  </code>
</pre>

<?php
$g->renamecol(null,"familyname","lastname","varchar(20)")->query();
echo '<code class="query">'.$g->query.'</code>';
//tab($g);
?>

<h3>Delete a column</h3>
...with the <code class="method">deletecol()</code> method:
<pre class="php">
  <code>
$g->deletecol("decibeltest","lastname")->query();
  </code>
</pre>

<?php
$g->deletecol("decibeltest","lastname")->query();
tab($g);
?>

<h3>Rename a table</h3>
...with the <code class="method">rename()</code> method:
<pre class="php">
  <code>
$g->rename(null,"decibelnice")->query();
  </code>
</pre>

<?php
$g->rename(null,"decibelnice")->query();
echo '<code class="query">'.$g->query.'</code>';
//tab($g);
?>

<h3>Describe a table</h3>
...with the <code class="method">describe()</code> method:
<pre class="php">
  <code>
$g->describe()->query()->populate()->inspect();
  </code>
</pre>

<?php
$g->describe();
echo '<code class="query">'.$g->query.'</code>';
$g->query()->populate()->inspect();
?>

<h3>Selections for pro!</h3>
...with the 
<code class="method">select()</code>,
<code class="method">where()</code>,
<code class="method">group()</code>,
<code class="method">order()</code>,
<code class="method">limit()</code>,
<code class="method">outfile()</code>,
<code class="method">fields()</code>,
<code class="method">lines()</code>,
<code class="method">offset()</code>,
methods.<br>

The options can also be in the wrong order and will be automatically reordered.

<pre class="php">
  <code>
$g->select(ALL)
  ->where("tx1<100")
  ->outfile("ciccio.dat")
  ->group("tx1")
  ->limit(2) 
  ->order("id",DESC)
  ->offset(0)
  ->fields(",","\n","'",OPT)
  ->lines("trm","strt");
  </code>
</pre>

I am not going to execute it :-) Just look at the order of the options in the query string:<br>

<?php
$g->select(ALL)
->where("tx1<100")
->outfile("ciccio.dat")
->group("tx1")
->limit(2)
->order("id",DESC)
->offset(0)
->fields(",","\n","'",OPT)
->lines("trm","strt");
echo '<code class="query">'.$g->query.'</code>';
?>

But wait, how can I show the query? And a part of it? Those strings
are stored in properties which have the same name of the method who
use them. So you can use <code>echo</code> to print them:
<pre class="php">
  <code>
echo '&lt;code&gt;'.$g->limit.'&lt;/code&gt;';
echo '&lt;code&gt;'.$g->where.'&lt;/code&gt;';
echo '&lt;code&gt;'.$g->fields.'&lt;/code&gt;';
echo '&lt;code&gt;'.$g->query.'&lt;/code&gt;';
  </code>
</pre>

<?php
echo '<code class="query">'.$g->limit.'</code>';
echo '<code class="query">'.$g->where.'</code>';
echo '<code class="query">'.$g->fields.'</code>';
echo '<code class="query">'.$g->query.'</code>';
?>

<h3>Inheritance</h3>

Set some other property and query the database: the previously methods are not inherited

<pre class="php">
  <code>
$g->select(['id','name'])
  ->where(['id<5','id>1'])
  ->order('datein')
  ->limit(1);
  </code>
</pre>

<?php
$g->select(array('id','name'))
->where(array('id<5','id>1'))
->order('datein')
->limit(1);
echo '<code class="query">'.$g->query.'</code>';
?>

<h3>Generic query</h3>
<pre class="php">
  <code>
$myquery="SELECT name FROM decibelnice LIMIT 2";
$g->query($myquery)->compact()->inspect();
  </code>
</pre>

<?php
$myquery="SELECT name FROM decibelnice LIMIT 2";
$g->query($myquery);
echo '<code class="query">'.$g->query.'</code>';
$g->compact()->inspect();
?>

<h3>Show databases or tables</h3>
...with the 
<code class="method">showdbs()</code> and
<code class="method">showtables()</code> methods:
<pre class="php">
  <code>
$g->showdbs();
$g->showtables();
  </code>
</pre>

<?php
$g->showdbs();
echo '<code class="query">'.$g->query.'</code>';
$g->showtables();
echo '<code class="query">'.$g->query.'</code>';
?>

<h3>Show metadata, columns name, columns width</h3>
...with the 
<code class="method">metadata()</code>,
<code class="method">colsname()</code> and
<code class="method">colswidth()</code> methods.<br>

These methods are specials, so they do not need to be populated.

<pre class="php">
  <code>
$g->metadata()->inspect();
$g->colsname()->inspect();
$g->colswidth()->inspect();
  </code>
</pre>

<?php
$g->metadata()->inspect();
$g->colsname()->inspect();
$g->colswidth()->inspect();
?>

<h3>Count the rows</h3>
...with the 
<code class="method">count()</code> method:
 
<pre class="php">
  <code>
$g->count()->show();
  </code>
</pre>

<?php
$g->count();
echo '<code class="query">'.$g->query.'</code>';
$g->inspect();
?>

<h3>Triggers</h3>

Create or drop triggers with the 
<code class="method">createtrigger()</code>
and <code class="method">droptrigger()</code> methods:

<pre class="php">
  <code>
$g->createtrigger(null,"asd","username=2");
echo $g->createtrigger;
$g->droptrigger("asd");
echo $g->droptrigger;
  </code>
</pre>

<?php
$g->createtrigger(null,"asd","username=2");
echo '<code class="query">'.$g->query.'</code>';
$g->droptrigger("asd");
echo '<code class="query">'.$g->query.'</code>';
?>


<h2>Summary</h2>

<p>This class is just the beginning of a greater work specific for
astronomy.  Please, use, modify, and debug at your ease. Doxygen
documentation will be implemented when it will be more complete.
<ul>
  <li>TODO: Learn PHP</li>
  <li>TODO: Find a way to pass the parameters
    directly as associative arrays.</li>
</ul>
<h2> Download</h2> 
<a href="decibel.v1.tar.gz">decibel.v1.tar.gz</a><br>
<a href="decibel.v1-ross.tar.gz">decibel.v1-ross.tar.gz (PHP<5.4.1)</a><br>
<a href="https://github.com/vertighel/decibel">...or fork me on github</a><br>
<p><small>Davide Ricci</small>

<script>
$('nav').toc({'selectors':'h3,h2', 'container':'body', 'smoothScrolling':true, 'highlightOnScroll':true});
$(window).width()<=1200 ? $('nav').hide() : $('nav').show();
$(window).bind('resize', function(){
    if ($(window).width()<=1200)
	$('nav').fadeOut();
    if ($(window).width()>1200)
	$('nav').fadeIn();
});
</script>

<?php
/// This is just to show what's happening
function tab($g){
  echo '<code class="query">'.$g->query.'</code>';
  $g->select(ALL)->query()->populate()->inspect();
}
?> 


<?php		   
/* /// Ross test */
/* $h=new decibel('ross.iasfbo.inaf.it','generic','password','test'); */
/* $ch=$h->colsname('simpleBSC')->last; /// same as $ch=$h->colsname; */
/* $h->select([$ch[0],$ch[1]])->limit(5) */
/* ->inspect('query') /// as an echo but on <pre><code> */
/* ->query()->compact()->inspect(); */

/* echo '<code>'.$h->query.'</code>'; */
/* $h->colsname('simpleBSC')->log(); */



/* /\* class debug (see the browser console) *\/ */

/* $deb = new debug; */
/* // simple message and warning */
/* $deb->log("message")->log("A simple Warning",null, WARN); */
/* // An Info */
/* $deb->log("A simple Info message", null, INFO); */
/* // An error */
/* $deb->log("A simple error messsage", null, ERR); */
/* // An object */
/* $book         = new stdClass; */
/* $book->title  = "Titolo"; */
/* $book->price  = 123; */
/* $deb->log(null, $book); */

/* /\* class testarea (a microscopic version of the class decibel) *\/ */

/* $ta=new testarea(); */
/* $ta->set_a()->show()->set_b()->show(); */
/* $ta->show('property_a'); */



?>

