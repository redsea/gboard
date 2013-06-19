BEGIN {
	FS = " "; 
	OFS = " ";
}
{
	code = $1;
	alpha2 = $2;
	
	if(alpha2 != "--") {
		printf("UPDATE gbd_country_code SET country_call_code = \"%s\" WHERE alpha2 = \"%s\";\n", code, alpha2);
	}
}
END {
}
