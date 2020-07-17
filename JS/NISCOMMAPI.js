function paddingLeft(str,lenght){
    /*向左補0*/
    if(str.length >= lenght)
        return str;
    else
        return paddingLeft("0" +str,lenght);
}
