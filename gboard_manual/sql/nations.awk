BEGIN {
	FS = "\t"; 
	OFS = "\t";
}
{
	name = $1;
	alpha2 = $2;
	alpha3 = $3;
	numberic = $4;

	printf("INSERT INTO gbd_nations (alpha2, alpha3, numberic, name, name_alias, country_call_code, c_date, u_date) VALUES (\"%s\", \"%s\", \"%s\", \"%s\", \"\", \"\", NOW()+0, \"\" );\n", alpha2, alpha3, numberic, name);
}
END {
}
