<?php

class decibel{

  private $host, $user, $pass, $db;

  /// Constructor
  public function __construct($host, $user, $pass, $db){
    $this->host=$host; // Host address
    $this->user=$user; // User
    $this->pass=$pass; // Password
    $this->db=$db;     // Database

    /// Defining keywords
    if (!defined("ALL")) define("ALL",1); // *
    if (!defined("COUNT")) define("COUNT",2); // count(...)
    if (!defined("COUNTALL")) define("COUNTALL",4); // count(*)
    if (!defined("ASC")) define("ASC",8); // ASC
    if (!defined("DESC")) define("DESC",16); // DESC
    if (!defined("OPT")) define("OPT",32); // OPTIONALLY 
    if (!defined("CLEAN")) define("CLEAN",64); // clean unsetted query methods
    if (!defined("DISTINCT")) define("DISTINCT",128); // select distinct
    if (!defined("KEYS")) define("KEYS",256); // keep only keys
    if (!defined("VALUES")) define("VALUES",512); // keep only values

    $this->connect();
    
    return;
  }


  /// Destructor
  public function __destruct(){
    $this->close();
  }


  /// Connect to the database
  public function connect(){
      $link=new mysqli($this->host,$this->user,$this->pass,$this->db);

    if($link->connect_errno)
      die('Unable to connect: '. $db->connect_error);
    else
      $this->link=$link;
  }


  /// SELECT ... DISTINCT FROM ...
  public function select($field=null,$table=null, $distinct=null){ 
    if($table===null)
      $table=$this->table;
    
    is_array($table) //json_decode?
      ? $t="`".implode($table, "`,`"). "`"
      : $t="`".$table."`";    
    
    if($field==ALL)
      $f='* ';
    elseif($field==COUNTALL)
      $f='count(*) ';
    else
      is_array($field)
	? $f="`".implode($field, "`, `"). "` "
	: $f="`".$field. "` ";

    if($distinct==DISTINCT)
      $d='DISTINCT ';
    else
      $d=null;

    /// Store original arguments
    $this->field=$field;
    $this->table=$table;
    /// Query string
    $this->select="SELECT ".$d.$f."FROM ".$t." ";
    $this->last=$this->select;         /// Store query string
    $this->hist= new stdClass;         /// Resetting the history for chain()
    $this->hist->select=$this->select; /// Pushing in the history
    $this->chain();                    /// Chain for the first time          

    return $this;
  }
  
  
  /// WHERE ...
  public function where($conditions=null){
    
    is_array($conditions)
      ? $c=implode($conditions, " AND ")." "
      : $c=$conditions." ";


    $this->conditions=$conditions;   /// Store original arguments
    $this->where="WHERE ".$c." ";    /// Query string
    $this->last=$this->where;        /// Store query string
    $this->hist->where=$this->where; /// Pushing in the history
    $this->chain();                  /// Chain with previous methods:

    return $this;
  }

  /// GROUP BY ...
  public function group($groupcol=null,$asc=null){

    $g="`".$groupcol."` ";

    if($asc===ASC)
      $a="ASC ";
    elseif($asc===DESC)
      $a="DESC ";
    else
      $a=null;

    $this->groupcol=$groupcol;       /// Store original arguments   
    $this->group="GROUP BY ".$g.$a;  /// Query string		    
    $this->last=$this->group;	     /// Store query string	    
    $this->hist->group=$this->group; /// Pushing in the history	    
    $this->chain();		     /// Chain with previous methods

    return $this;
  }


  /// ORDER BY ...
  public function order($ordercol=null,$asc=null){

    $o="`".$ordercol."` ";

    if($asc===ASC)
      $a="ASC ";
    elseif($asc===DESC)
      $a="DESC ";
    else
      $a=null;

    $this->ordercol=$ordercol;        /// Store original arguments   
    $this->order="ORDER BY ".$o.$a;   /// Query string		    
    $this->last=$this->order;	      /// Store query string	    
    $this->hist->order=$this->order;  /// Pushing in the history	    
    $this->chain();		      /// Chain with previous methods

    return $this;
  }

  
  /// LIMIT ...
  public function limit($limitval=null){

    $l=round((float)$limitval)." ";

    $this->limitval=$limitval;        /// Store original arguments   
    $this->limit="LIMIT ".$l;	      /// Query string		    
    $this->last=$this->limit;	      /// Store query string	    
    $this->hist->limit=$this->limit;  /// Pushing in the history	    
    $this->chain();		      /// Chain with previous methods

    return $this;
  }


  
  /// OFFSET ...
  public function offset($offsetval=null){ 

    $o=round((float)$offsetval)." ";

    $this->offsetval=$offsetval;       /// Store original arguments   
    $this->offset="OFFSET ".$o;	       /// Query string		    
    $this->last=$this->offset;	       /// Store query string	    
    $this->hist->offset=$this->offset; /// Pushing in the history	    
    $this->chain();		       /// Chain with previous methods

    return $this;
  }


  /// INTO OUTFILE ...
  public function outfile($filename=null){
    if($filename===null){
       unset($this->outfile);
       return $this;
    }
    
    $f='"'.$filename.'" ';
   
    $this->filename=$filename;           /// Store original arguments   
    $this->outfile="INTO OUTFILE ".$f;	 /// Query string		    
    $this->last=$this->outfile;		 /// Store query string	    
    $this->hist->outfile=$this->outfile; /// Pushing in the history	    
    $this->chain();			 /// Chain with previous methods

    return $this;
  }
  

  /// FIELDS TERMINATED BY ... OPTIONALLY ENCLOSED BY ... ESCAPED BY ...
  public function fields($terminated=null,$enclosed=null,$escaped=null,$opt=null){ 

    $t='"'.$terminated.'" ';
    $n='"'.$enclosed.'" ';
    $s='"'.$escaped.'" ';

    $this->fields="FIELDS TERMINATED BY ".$t;    
    if($opt==OPT)
      $this->fields.="OPTIONALLY ";
    if(isset($enclosed))
      $this->fields.="ENCLOSED BY ".$n;
    if(isset($escaped))
      $this->fields.="ESCAPED BY ".$s;

    /// Store original arguments
    $this->terminated=$terminated;
    $this->enclosed=$enclosed;
    $this->escaped=$escaped;
    /// Query string
    $this->fields="FIELDS TERMINATED BY ".$t;    
    if($opt==OPT)
      $this->fields.="OPTIONALLY ";
    if(isset($enclosed))
      $this->fields.="ENCLOSED BY ".$n;
    if(isset($escaped))                 
      $this->fields.="ESCAPED BY ".$s;	    
    $this->last=$this->fields;           /// Store query string	        
    $this->hist->fields=$this->fields;	 /// Pushing in the history	    
    $this->chain();			 /// Chain with previous methods

    return $this;
  }


  /// LINES STARTING BY ... TERMINATED BY ...
  public function lines($terminated=null,$starting=null){ 

    $t='"'.$terminated.'" ';
    $s='"'.$starting.'" ';

    /// Store original arguments
    $this->terminated=$terminated;
    $this->starting=$starting;
    /// Query string
    $this->lines="LINES TERMINATED BY ".$t;
    if(isset($starting))                                          
      $this->lines="LINES STARTING BY ".$s."TERMINATED BY ".$t;
    $this->last=$this->lines;          /// Store query string	     
    $this->hist->lines=$this->lines;   /// Pushing in the history	    
    $this->chain();		       /// Chain with previous methods

    return $this;
  }


  /// IGNORE ... LINES
  public function ignore($ignoreval){ // ['\n'] 

    $i=round((float)$ignoreval)." ";

    $this->ignoreval=$ignoreval;          /// Store original arguments   
    $this->ignore="IGNORE ".$i." LINES "; /// Query string		    
    $this->last=$this->ignore;		  /// Store query string	    
    $this->hist->ignore=$this->ignore;	  /// Pushing in the history	    
    $this->chain();			  /// Chain with previous methods

    return $this;
  }


  /// Insert or Replace helper method. 
  /// The first argument will set which one
  private function insertorreplace($ir,$table=null, $columns=null, $values=null){
    if($table===null)
      $table=$this->table;
    
    if($values===null && $columns!==null){
      $val=$columns; // in this case the first argument are the vlaues
      $this->values=$columns; /// Store original argument
      $c=null; // I will not need to set this
    }else{
      $val=$values;
      is_array($columns) //json_decode?
	? $c=" (`".implode($columns,"`,`")."`) "
	: $c=" `".$columns."` ";
      /// Store original arguments
      $this->columns=$columns; 
      $this->values=$values; 
    }

    /// Values can be a string, an array (one line), 
    /// or an array of arrays (multiple lines)
    if(is_array($val[0])){ // multidimensional?
      $lines=array();
      foreach($val as $line)
	$lines[]="('".implode($line,"','"). "')";
      $v =" VALUES ".implode(", ",$lines);
    }else{
      is_array($val) // simple array?
	? $v=" VALUES ('".implode($val,"','"). "') "
	: $v=" VALUES '".$val."' "; // string?
      }

    $this->table=$table;       /// Store original arguments
    $this->hist= new stdClass; /// Resetting the methods history string

    /// The first argument is "insert" or "replace"?
    if($ir==="insert"){
      $this->insert="INSERT INTO ".$table.$c.$v;   /// Query string
      $this->hist->insert=$this->insert;           /// Pushing in the history
      $this->last=$this->insert;                   /// Store query string
    }elseif($ir==="replace"){
      $this->replace="REPLACE INTO ".$table.$c.$v; /// Query string	     
      $this->hist->replace=$this->replace;         /// Pushing in the history
      $this->last=$this->replace;                  /// Store query string
    }
    $this->chain(); /// Chain with previous methods

    return $this;
  }

  
  /// INSERT INTO ... (...) VALUES (...)
  public function insert($table=null, $columns=null, $values=null){
    $this->insertorreplace("insert",$table, $columns, $values);
    return $this;
  }      
  

  /// REPLACE INTO ... (...) VALUES (...)
  public function replace($table=null, $columns=null, $values=null){
    $this->insertorreplace("replace",$table, $columns, $values);
    return $this;
  }      
  

  /// DUPLICATE KEY UPDATE ...
  public function duplicate($dupkey=null){ // "colvalue=colvalue+10"
    $d=$dupkey." ";

    $this->dupkey=$dupkey;                            /// Store original arguments   
    $this->duplicate=' ON DUPLICATE KEY UPDATE '.$d;  /// Query string		     
    $this->last=$this->duplicate;		      /// Store query string	     
    $this->hist->duplicate=$this->duplicate;	      /// Pushing in the history     
    $this->chain();				      /// Chain with previous methods

    return $this;
  }
  
  
  /// DELETE FROM ... 
  public function delete($table=null,$where=null){ 
    if($table===null)
      $table=$this->table;
    
    if($where!==null){
     trigger_error("If this was intended to be a 'where', 
                    please chain the where() method instead");
     exit;
    }
    
    is_array($table) //json_decode?
      ? $t="`".implode($table, "`, `")."` "
      : $t="`".$table. "` ";
    
    $this->hist= new stdClass; /// Resetting the methods history string
    $this->table=$table;               /// Store original arguments   
    $this->delete="DELETE FROM ".$t;   /// Query string		     
    $this->last=$this->delete;	       /// Store query string	     
    $this->hist->delete=$this->delete; /// Pushing in the history     
    $this->chain();      	       /// Chain with previous methods

    return $this;
  }
  
  
  /// UPDATE ... SET ...=... 
  public function update($table=null,$columns=null){
    if($table===null)
      $table=$this->table;
    
    is_array($table) //json_decode?
      ? $t="`".implode($table, "`, `"). "` "
      : $t="`".$table. "` ";
    $this->table=$table;

    is_array($columns) //json_decode?
      ? $c=implode($columns, ",")." "
      : $c=$columns." ";

    $this->hist= new stdClass; /// Resetting the methods history string
    $this->columns=$columns;               /// Store original arguments   
    $this->update="UPDATE ".$t." SET ".$c; /// Query string		     
    $this->last=$this->update;		   /// Store query string	     
    $this->hist->update=$this->update;	   /// Pushing in the history     
    $this->chain();			   /// Chain with previous methods

    return $this;
  }
  


  /// ALTER TABLE ... ADD ... ...
  public function addcol($table=null,$columns=null){//,$types=null){
    if($table===null)
      $table=$this->table;
    
    is_array($table) //json_decode?
      ? $t="`".implode($table, "`, `"). "` "
      : $t="`".$table. "` ";
    $this->table=$table;

    is_array($columns) //json_decode?
      ? $c="(".implode($columns, ",").") "
      : $c=$columns." ";

    /* is_array($columns) //json_decode? */
    /*   ? $c="(`".implode($columns, "`,`")."`) " */
    /*   : $c="`".$columns."` "; */

    /* is_array($types) //json_decode? */
    /*   ? $y="(".implode($types, ",").") " */
    /*   : $y=$types." "; */
    $y=null;

    $this->hist= new stdClass; /// Resetting the methods history string
    $this->columns=$columns;                      /// Store original arguments   
    /* $this->types=$types;                          /// Store original arguments    */
    $this->addcol="ALTER TABLE ".$t."ADD ".$c.$y; /// Query string		     
    $this->last=$this->addcol;			  /// Store query string	     
    $this->hist->addcol=$this->addcol;		  /// Pushing in the history     
    $this->chain();				  /// Chain with previous methods

    return $this;
  }
  

  /// ALTER TABLE ... CHANGE ... ... ...;
  public function renamecol($table=null,$oldname,$newname,$type){
    if($table===null)
      $table=$this->table;
    
    $t="`".$table."` ";
    $this->table=$table;

    $o="`".$oldname."` ";

    $n="`".$newname."` ";

    $y=$type." ";

    $this->hist= new stdClass; /// Resetting the methods history string
    /// Store original arguments
    $this->oldname=$oldname;
    $this->newname=$newname;
    $this->type=$type;                                     
    $this->renamecol="ALTER TABLE ".$t."CHANGE ".$o.$n.$y; /// Query string	     
    $this->last=$this->renamecol;		   /// Store query string   
    $this->hist->renamecol=$this->renamecol;	   /// Pushing in the history   
    $this->chain();				   /// Chain with previous methods

    return $this;
  }
  

  /// ALTER TABLE ... DROP COLUMN ...
  public function deletecol($table=null,$delname){
    if($table===null)
      $table=$this->table;
    
    $t="`".$table."` ";
    $this->table=$table;
    $d="`".$delname."` ";
    
    $this->hist= new stdClass; /// Resetting the methods history string
    $this->delname=$delname;                      /// Store original arguments 
    $this->deletecol="ALTER TABLE ".$t."DROP COLUMN ".$d; /// Query string	     
    $this->last=$this->deletecol;	       	  /// Store query string     
    $this->hist->deletecol=$this->deletecol;	  /// Pushing in the history    
    $this->chain();				  /// Chain with previous methods

    return $this;
  }
  

  /// DESCRIBE ...
  public function describe($table=null){
    if($table===null)
      $table=$this->table;
    
    $t="`".$table."` ";    

    $this->hist= new stdClass; /// Resetting the methods history string
    $this->table=$table;                   /// Store original arguments   
    $this->describe="DESCRIBE ".$t;	   /// Query string		     
    $this->last=$this->describe;	   /// Store query string	     
    $this->hist->describe=$this->describe; /// Pushing in the history     
    $this->chain();			   /// Chain with previous methods

    return $this;
  }

 
  /// CREATE TABLE IF NOT EXISTS ... (...)
  public function create($table=null,$conditions=null){
    if($table===null)
      $table=$this->table;
    
    $t="`".$table."` ";
    $this->table=$table;

    /// Values can be a string, an array (one element per column), 
    /// or an array of arrays (associative array? to be implemented)
    if(is_array($conditions[0])){ // multidimensional?
      $lines=array();
      foreach($conditions as $line)
	$lines[]=implode($line,",");
      $c=implode(", ",$lines);
    }else{
      is_array($conditions) // simple array?
	? $c=implode($conditions,",")
	: $c=$conditions; // string?
    }

    $conditions===null
      ? $c=null
      : $c="(".$c.") ";
      
    $this->hist= new stdClass; /// Resetting the methods history string
    $this->conditions=$conditions;                     /// Store original arguments   
    $this->create="CREATE TABLE IF NOT EXISTS ".$t.$c; /// Query string		     
    $this->last=$this->conditions;		       /// Store query string	     
    $this->hist->create=$this->create;		       /// Pushing in the history     
    $this->chain();				       /// Chain with previous methods

    return $this;
  }


  /// LIKE ...
  public function like($table){
    if($table===null)
      $table=$this->table;

    $t="`".$table."` ";    
    $this->like='LIKE '.$t;     /// Query string		     
    $this->last=$this->like;	       /// Store query string	     
    $this->hist->like=$this->like;     /// Pushing in the history     
    $this->chain();		       /// Chain with previous methods

    return $this;
  }

  
  /// RENAME TABLE ... TO ...
  public function rename($table=null,$newname){
    if($table===null)
      $table=$this->table;
    $t="`".$table."` ";    

    $this->table=$newname; /// more useful!!!!!!!!!!!!!!!!!!!!!!!

    $n="`".$newname."` ";

    $this->hist= new stdClass; /// Resetting the methods history string
    $this->newname=$newname;                   /// Store original arguments   
    $this->rename="RENAME TABLE ".$t."TO ".$n; /// Query string		     
    $this->last=$this->rename;		       /// Store query string	     
    $this->hist->rename=$this->rename;	       /// Pushing in the history     
    $this->chain();			       /// Chain with previous methods

    return $this;
  }


  /// DROP TABLE ...
  public function drop($table){
    if($table===null)
      $table=$this->table;        
    $t="`".$table."` ";    
    $this->table=$table;

    $this->hist= new stdClass; /// Resetting the methods history string
    $this->drop='DROP TABLE IF EXISTS '.$t; /// Query string	    
    $this->last=$this->drop;		    /// Store query string	     
    $this->hist->drop=$this->drop;	    /// Pushing in the history     
    $this->chain();			    /// Chain with previous methods

    return $this;
  }


  /// DROP TRIGGER IF EXISTS ...
  public function droptrigger($trigger){
    if($trigger===null)
      $trigger=$this->trigger;
    $r="`".$trigger."` ";    

    $this->trigger=$trigger;                         /// Store original arguments   
    $this->droptrigger='DROP TRIGGER IF EXISTS '.$r; /// Query string		     
    $this->last=$this->droptrigger;		     /// Store query string
    $this->hist->droptrigger=$this->droptrigger;     /// Pushing in the history     
    $this->chain();				     /// Chain with previous methods

    return $this;
  }

  
  /// CREATE TRIGGER IF EXISTS ...
  public function createtrigger($table,$trigger,$conditions){
    if($table===null)
      $table=$this->table;
    $t="`".$table."` ";    
    $this->table=$table;

    $r="`".$trigger."` ";    

    /// Values can be a string, an array (one element per condition), 
    /// or an array of arrays (associative array? to be implemented)
    if(is_array($conditions[0])){ // multidimensional?
      $lines=array();
      foreach($conditions as $line)
	$lines[]=implode($line,",");
      $c=implode(", ",$lines);
    }else{
      is_array($conditions) // simple array?
	? $c="SET NEW.".implode($conditions,"; SET NEW.")
	: $c="SET NEW.".$conditions; // string?
    }    
        
    $this->hist= new stdClass; /// Resetting the methods history string
    $this->trigger=$trigger;                         /// Store original arguments   
    $this->conditions=$conditions;                   /// Store original arguments   
    $this->createtrigger='CREATE TRIGGER '.$r       /// Query string
      .'BEFORE INSERT ON '.$t.'FOR EACH ROW BEGIN ' 
      .$c.'; END';
    $this->last=$this->createtrigger;		     /// Store query string	     
    $this->hist->createtrigger=$this->createtrigger; /// Pushing in the history     
    $this->chain();				     /// Chain with previous methods

    return $this;
  }
  

  /// Chain the methods to create the query string
  /// using the history of setted methods or using all of them.
  public function chain($hist=null){
    if($hist===null)      /// Using the history
      $base=$this->hist;
    elseif($hist===ALL){  /// Using all the setted methods
      $base=$this;
    }

    /// Select
    if(isset($base->select)){
          $c=$base->select;
      if(isset($base->where))
	   $c.=$base->where;
      if(isset($base->group))
           $c.=$base->group;
      if(isset($base->order))
           $c.=$base->order;
      if(isset($base->limit))
           $c.=$base->limit;
      if(isset($base->offset))
           $c.=$base->offset;
      if(isset($base->outfile)){ 
	   $c.=$base->outfile;
	if(isset($base->fields))
	     $c.=$base->fields;
        if(isset($base->lines))
             $c.=$base->lines;
        if(isset($base->ignore))
             $c.=$base->ignore;
      }
    }

    /// Insert (element)
    if(isset($base->insert)){
          $c=$base->insert;
      if(isset($base->duplicate))
           $c.=$base->duplicate;
    }

    /// Replace element
    if(isset($base->replace)){
          $c=$base->replace;
      if(isset($base->duplicate))
           $c.=$base->duplicate;
    }

    /// Delete element
    if(isset($base->delete)){
          $c=$base->delete;
      if(isset($base->where))
           $c.=$base->where;
      if(isset($base->order))
           $c.=$base->order;
      if(isset($base->limit))
           $c.=$base->limit;
    }

    /// Update element
    if(isset($base->update)){
          $c=$base->update;
      if(isset($base->where))
           $c.=$base->where;
      if(isset($base->order))
           $c.=$base->order;
      if(isset($base->limit))
           $c.=$base->limit;
    }

    /// Alter (Add, Rename, Delete column)
    if(isset($base->addcol))
          $c=$base->addcol;

    if(isset($base->renamecol))
          $c=$base->renamecol;

    if(isset($base->deletecol))
          $c=$base->deletecol;

    /// Describe table
    if(isset($base->describe))
          $c=$base->describe;

    /// Create table
    if(isset($base->create)){
          $c=$base->create;
      if(isset($base->like))
           $c.=$base->like;
    }

    /// Rename table
    if(isset($base->rename))
          $c=$base->rename;

    /// Drop table
    if(isset($base->drop))
          $c=$base->drop;

    /// Create trigger
    if(isset($base->createtrigger))
          $c=$base->createtrigger;

    /// Drop trigger
    if(isset($base->droptrigger))
          $c=$base->droptrigger;

    $this->query=$c;	
    $this->last=$this->query;  /// Store query string	     

    return $this->query;       /// Returns the query string and not himself!
  }

 
  /// Query the database
  public function query($query=null){
    if($query===ALL)        /// - Less common: All setted methods since the connection!
      $q=$this->chain(ALL); ///   Re-chaining all
    elseif($query===null)   /// - Most common: only the method setted before this query
      $q=$this->query;      ///   They were are already chained.  
    else{                   /// - Generic query
      $q=$query;            ///   Just take this string
      $this->query=$q;      ///   and store it
    }
    
    $stm=$this->link->prepare($q) 
      or trigger_error($this->link->error);
    $stm->execute()
      or trigger_error($this->link->error);

    $this->result=$stm->get_result();
    
    $this->affected=$stm->affected_rows;  /// Affected rows
    if(isset($this->result->num_rows))    /// When provided
      $this->num_rows=$this->result->num_rows;
    
    $this->last=$this->result; /// Contains the informations about the results

    return $this;
  }


  /// Populate an array with the results;
  /// Also can choose if having key:value (default),
  /// only KEYS, or only VALUES.
  public function populate($keyval=null){    
    $rows = array();
    
    while($row=$this->last->fetch_assoc()){ // this last or this result?
      if($keyval===KEYS)
	$rows[]=array_keys($row);   /// [["id","age"],["id","age"]]
      elseif($keyval===VALUES)
	$rows[]=array_values($row); /// [[1,4],[2,4]]
      else
	$rows[]=$row;      /// default: [{"id":1,"age":4},{"id":2,"age":4}]
    }

    $this->rows=$rows; 
    $this->last=$this->rows;

    return $this;
  }


  /// Some aliases to get keys or values
  public function keys(){ return $this->populate(KEYS); } 
  public function values(){ return $this->populate(VALUES); }


  /// Compact the results: an alternative way to obtain them
  /// by "dividing" them in one array for each column;
  public function compact(){
    $rows = array();

    while($row=$this->result->fetch_assoc())
      foreach($row as $field=>$value)
	$rows[$field][]=$value;  /// {"id":[1,2],"age":[4,4]}
    
    $this->rows=$rows;    
    $this->last=$this->rows;

    return $this;
  }


  /// Helper function to show database names or table names
  private function showdbsortables($dot){
    $rows = array();

    if($dot=="databases"){
      $this->query="SHOW DATABASES ";
      $this->showdbs=$this->query;
    }elseif($dot=="tables"){
      $this->query="SHOW TABLES ";
      $this->showtables=$this->query;
    }
    /* $this->query($this->query); */
    $this->last=$this->query;

    /* while($row=$this->result->fetch_row()) */
    /*   foreach($row as $field) */
    /* 	$rows[]=$field; */

    return $this;
  }


  /// SHOW DATABASES (...)
  public function showdbs(){
    $this->showdbsortables("databases");
    return $this;
  }      


  /// SHOW TABLES (...)
  public function showtables(){
    $this->showdbsortables("tables");
    return $this;
  }      


  /// Metadata of a table
  public function metadata($table=null){
    if($table===null)
      $table=$this->table;    

    $this->select(ALL,$table)
      ->limit(0)
      ->query();
    
    $this->table=$table;
    $this->metadata=$this->result->fetch_fields();

    $this->last=$this->metadata;
    return $this;
  }


  /// Generic property of a metadata object (name, length)
  public function colsproperty($table=null,$prop){
    if($table===null)
      $table=$this->table;
    
    $this->metadata($table);
    
    foreach($this->metadata as $m){
      $c[]=$m->{$prop}; //name
    }

    $this->colsproperty=$c;

    $this->table=$table;
    $this->last=$this->colsproperty;
    return $this;
  }


  /// Column Names ('name')
  public function colsname($table=null){    
    if($table===null)
      $table=$this->table;
    
    $this->colsproperty($table,'name');
    
    $this->colsname=$this->colsproperty;

    $this->table=$table;
    $this->last=$this->colsname;
    return $this;
  }


  /// Column widths (length)
  public function colswidth($table=null){
    if($table===null)
      $table=$this->table;
    
    $this->colsproperty($table,'length');
    
    $this->colswidth=$this->colsproperty;

    $this->table=$table;
    $this->last=$this->colswidth;
    return $this;
  }


  /// counting the rows
  public function count($table=null){
    if($table===null)
      $table=$this->table;

    $this->select(COUNTALL,$table)->query();

    //    $result=$this->link->query("select count(*) from ".$table);
    $row=$this->result->fetch_row() 
      or trigger_error($this->link->error);

    $this->count=$row[0];

    $this->table=$table;
    $this->last=$this->count;
    return $this;
    
  }


  /// Show the value of the last property setted
  public function show($va=null){
    if($va==null) // Last
      echo json_encode($this->last, JSON_NUMERIC_CHECK);
    elseif ($va==ALL) // All
      echo json_encode($this, JSON_NUMERIC_CHECK);
    else // Selected
      echo json_encode($this->{$va}, JSON_NUMERIC_CHECK);
    
    return $this->query();
  }


  /// Inspect the value of a property in pretty <pre><code> tags
  public function inspect($va=null){
    echo PHP_EOL.'<pre>'.PHP_EOL.'  <code>'.PHP_EOL;
    if($va==null) // Last
      echo json_encode($this->last, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK); 
    elseif ($va==ALL) // All
      echo json_encode($this, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
    else // Selected
      echo json_encode($this->{$va}, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
    echo PHP_EOL.'  </code>'.PHP_EOL.'</pre>'.PHP_EOL;

    return $this->query();
  }


  /// Show the value of a property in the js console
  public function log($va=null){
    $deb=new debug;
    if($va==null) // Last
      $deb->log(null,$this->last);
    elseif ($va==ALL) // All
      $deb->log(null,$this);
    else // Selected
      $deb->log(null,$this->{$va});
    echo '</pre></code>';

    return $this->query();
  }


   /// Close the link connection
  public function close(){
    return $this->link->close() 
      or trigger_error($this->link->error);
  }


} /* class decibel */



class debug{
  
  /// Constructor
  public function __construct(){
    if (!defined("LOG"))    define("LOG",1);
    if (!defined("INFO"))   define("INFO",2);
    if (!defined("WARN"))   define("WARN",3);
    if (!defined("ERR"))    define("ERR",4);

    return;
  }
    
  /// Display a log
  public function log($name, $var=null, $type=LOG){

    echo '<script>'.PHP_EOL.'// php debug'.PHP_EOL;
    
    switch($type) {
    case LOG:
      echo 'console.log("'.$name.'");'.PHP_EOL;    
      break;
    case INFO:
      echo 'console.info("'.$name.'");'.PHP_EOL;    
      break;
    case WARN:
      echo 'console.warn("'.$name.'");'.PHP_EOL;    
      break;
    case ERR:
      echo 'console.error("'.$name.'");'.PHP_EOL;    
      break;
    }
    
    if (!empty($var)){
      if (is_object($var) || is_array($var)){
	$nameclean=preg_replace('~[^A-Z|0-9]~i',"_",$name);
	$object=json_encode($var);

	echo 'var object'.$nameclean.'=\''.str_replace("'","\'",$object).'\';'.PHP_EOL;
	echo 'var val'.$nameclean.'=eval("(" + object'.$nameclean.' + ")" );'.PHP_EOL;
	
	switch($type){
	case LOG:
	  echo 'console.debug(val'.$nameclean.');'.PHP_EOL;    
	  break;
	case INFO:
	  echo 'console.info(val'.$nameclean.');'.PHP_EOL;
	  break;
	case WARN:
	  echo 'console.warn(val'.$nameclean.');'.PHP_EOL;        
	  break;
	case ERR:
	  echo 'console.error(val'.$nameclean.');'.PHP_EOL;    
	  break;
	}
      }else{
	$varclean=str_replace('"','\\"',$var);
	switch($type) {
	case LOG:
	  echo 'console.debug("'.$varclean.'");'.PHP_EOL;
	  break;
	case INFO:
	  echo 'console.info("'.$varclean.'");'.PHP_EOL;
	  break;
	case WARN:
	  echo 'console.warn("'.$varclean.'");'.PHP_EOL;    
	  break;
	case ERR:
	  echo 'console.error("'.$varclean.'");'.PHP_EOL;
	  break;
	}
      }
    }
    
    echo '</script>'.PHP_EOL;

    return $this;    
  } 

} /* class debug */


class testarea{
  
  public function set_a(){
    $this->property_a='this is a';
    $this->last=$this->property_a;
    return $this;
  }

  public function set_b(){
    $this->property_b=123;
    $this->last=$this->property_b;
    return $this;
  }

  public function show($va=null){
    echo json_encode($this->last); // all
    return $this;
  }

} /* class testarea */



