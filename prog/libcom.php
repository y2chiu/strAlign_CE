<?php

    set_time_limit(0);
    ini_set('memory_limit','1024M');

    if(!defined('STDERR')) 
        define('STDERR', fopen('php://stderr','w'));


        


	function _checkFile($fname, $s='', $e=-1)
    {
        if(!file_exists($fname))    
        {
            if(empty($s))
                fprintf(STDERR, " No file: %s\n", $fname);
            else
                fprintf(STDERR, $s);

            exit($e);
        }
	}

    function checkFileExists($fname, $s='')
    {
		if(is_array($fname))
			foreach($fname as $f)	_checkFile($f, $s);
		else
									_checkFile($fname, $s);
    }


    function errexit($s, $e=-1)
    {
        fwrite(STDERR, $s);
        exit($e);
    }


    function getRealPath($path)
    {
        if(($p = realpath($path)) !== false) 
            return $p;
        else
        {
            fwrite(STDERR, sprintf(" !! Error, no path (%s).\n", $path));
            exit;
        }
    }

    function createPath($path)
    {
        if(!is_dir($path))    
            system(sprintf('mkdir -p %s', $path));

        return realpath($path);
    }



    function getOption($shortopt, $longopt=array())
    {
        global $argv;

        $input_opt = $argv;

        if(!is_array($longopt))
            return;
        elseif (count($longopt))
            $longopt = sprintf('-l %s', join(',',$longopt));
        else
            $longopt = '';


        $prog = basename(array_shift($input_opt));
        $args = join(' ', $input_opt);
        $cmd  = sprintf('getopt -n %s -o %s %s -- %s'
                        , $prog, $shortopt, $longopt, $args);

        $opts = shell_exec($cmd);
        $opts = preg_split('/\s+/', trim($opts));

        
        $param = array();
        $cur_opt = false;

        for($i=0,$n=count($opts);$i<$n;$i++)
        {
            $op = $opts[$i];

            if($op == '--')
            {
                for($j=$i+1,$k=1;$j<$n;$j++,$k++)
                    $param[$k] = trim($opts[$j], "'"); 

                return $param;
            }

            else if($op[0] == '-')
            {
                $cur_opt = trim($op, '-');

                if(false === isset($param[$cur_opt]))
                {
                    $param[$cur_opt] = false;
                }
            }

            else if (false !== $cur_opt)
            {
                //if(false === $result[$current_key])
                {
                    $param[$cur_opt] = trim($op, "'");
                }
            }
        }

        return $param;

    }



    /*
    $opt = getOption('d:na:',array('do:'));
    print_r($opt);
    */

?>
