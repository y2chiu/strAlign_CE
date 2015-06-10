PDB files from CE structual alignment results
=============================================

##Steps

1. Set the CE path
  >In prog/0_param.ini   
  >  
  >[CE_parameters]  
  >CE_DIR = 'ce_distr'  
  >CE_PROG = 'CErun'  

2. Prepare the PDB files

3. Create list file including ID, CHAIN, FILE_NAME
  > INPUT FORMAT  
  > using first line as reference  
  > for example, 2NRY chain A will be aligned to 2NRU chain A and output name is 2nry_A
  >
  >ID      CHAIN   FILE_NAME 
  >2nru_A  A       2nru.pdb
  >2nry_A  A       2nry.pdb
  >2o8y_A  A       2o8y.pdb

4. Run CE COMMAND
  ```
  # Run CE
  #prog/1_runCE.php -l LIST_PDB --di DIR_INPUT_PDB --do DIR_CE_OUT
  #
  #Parameters
  #-l   [input list of PDB files you want to align]
  #--di [directory of you prepared PDB files]
  #--do [directory of CE output results]

  #Command
  php prog/1_runCE.php -l example/list.IRAK4.13str.txt --di example/IRAK4/ --do example/IRAK4_ceout

  # Generate aligned PDBs
  #prog/2_mat2PDB.php -l LIST_PDB --dm DIR_CE_OUT --di DIR_INPUT_PDB ­do DIR_ALIGN_PDB 
  #
  #Parameters
  #-l   [input list of PDB files you want to align]
  #--dm [directory of CE output results]
  #--di [directory of you prepared PDB files]
  #--do [directory of new aligned PDB files]

  #Command
  php prog/2_mat2PDB.php -l example/list.IRAK4.13str.txt --dm example/IRAK4_ceout/ --di example/IRAK4 --do example/IRAK4_trpdb
  ```

5. GET RMSD
  ```
  #Calculate RMSDs
  #prog/4_getRMSD.php -l LIST_PDB --dm DIR_CE_OUT
  #
  #Parameters
  #-l   [input list of PDB files you want to align]
  #--dm [directory of CE output results]

  #Command
  php prog/4_getRMSD.php -l example/list.IRAK4.13str.txt --dm example/IRAK4_ceout/
  ```
