<?
    $DIR_PROG = dirname(realpath(__FILE__));
    include($DIR_PROG.'/libcom.php');
    ini_set('memory_limit', '2048M');

    $opt= getOption('m:i:o:c:', array('di:','do:'));
    foreach(array('m','i','c','do') as $k)
    {
        if(!isset($opt[$k]))
            die(sprintf("\n !! %s -m FN_CE_MATRIX -c CHAIN -i FILE_INPUT --do DIR_OUT [-o FILE_OUTPUT]\n\n", $argv[0]));
    }

    $FN_MAT  = getRealPath($opt['m']);
    $CH_IN   = $opt['c'];
    $FN_IN   = getRealPath($opt['i']);
    $FN_OUT  = isset($opt['o']) ? basename($opt['o']) : basename($opt['i']);
    $DIR_OUT = createPath($opt['do']);

    include('transPDB.php');

    $cmd = sprintf('cat %s | tail -n 3', $FN_MAT);
    $out = explode("\n",trim(shell_exec($cmd)));

    $u = $t = array();
    foreach($out as $r)
    {
        $m = preg_split("#[\(\)]#", $r);
        if(count($m) < 8)
        {
            fprintf(STDERR, " !! Error, no matrix of %s\n", $ID_PDB);
            exit;
        }
        array_push($u, $m[1], $m[3], $m[5]);
        array_push($t, $m[7]);
    }

    $trmat = array_merge($u, $t);
    $trmat = array_map('trim', $trmat);
    $trmat = array_map('doubleval', $trmat);

    $pname = sprintf('%s', $FN_IN);
    $oname = sprintf('%s/%s', $DIR_OUT, $FN_OUT);

    trans_structure($pname, $oname, $trmat, $CH_IN);
?>
