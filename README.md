1. Prepare the PDB files
2. Create list file including ID, CHAIN, FILE_NAME
// INPUT FORMAT
// using first line as reference
// for example, 3h9r chain A will be aligned to 2jdr chain A
//
//#ID     CHAIN   FILE_NAME
//2jdr_A  A       30_kinase_cav/AKT2_AGC_2jdr_A.pdb
//3h9r_A  A       30_kinase_cav/ACVR1_TKL_3h9r_A.pdb

3. COMMAND
php prog/1_runCE.php -l example/list.32_kinase_cav.txt --di example/32_kinase_cav/ --do example/32_kinase_cemat > todo1.sh
sh todo1.sh
php prog/2_mat2PDB.php -l example/list.32_kinase_cav.txt --dm example/32_kinase_cemat/ --di example/32_kinase_cav/ --do example/32_kinase_trpdb > todo2.sh
sh todo2.sh

4. GET RMSD
php prog/4_getRMSD.php -l example/list.32_kinase_cav.txt --dm example/32_kinase_cemat/
