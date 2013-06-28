BEGIN {
	FS = "\t"; 
	OFS = "\t";
}
{
	alpha3 = $1;
	alpha2 = $2;
	name_ko = $3;
	name_en = $4;
	
	if(alpha3 == "" || alpha2 == "" || name_ko == "" || name_en == "") {
		printf("error in %s", alpha3);
		exit;
	}

	if(alpha2 == "-") {
		printf("INSERT INTO gbd_language_code (alpha2, alpha3, description1, description2, c_date, u_date) VALUES (\"\", \"%s\", \"%s\", \"%s\", NOW()+0, \"\" );\n", alpha3, name_ko, name_en);
	} else {
		printf("INSERT INTO gbd_language_code (alpha2, alpha3, description1, description2, c_date, u_date) VALUES (\"%s\", \"%s\", \"%s\", \"%s\", NOW()+0, \"\" );\n", alpha2, alpha3, name_ko, name_en);
	}
}
END {
}
