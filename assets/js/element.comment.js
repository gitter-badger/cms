	var a=new Array();
	var b=new Array();

	var single = new Array();
	var j = new Array();
	var h = new Array();

	single['a'] = 'а';
	single['b'] = 'б';
	single['v'] = 'в';
	single['g'] = 'г';
	single['d'] = 'д';
	single['e'] = 'е';
	single['z'] = 'з';
	single['i'] = 'и';
	single['k'] = 'к';
	single['l'] = 'л';
	single['m'] = 'м';
	single['n'] = 'н';
	single['o'] = 'о';
	single['p'] = 'п';
	single['r'] = 'р';
	single['s'] = 'с';
	single['t'] = 'т';
	single['u'] = 'у';
	single['f'] = 'ф';
	single['c'] = 'ц';
	single['y'] = 'ы';
	single['"'] = 'ъ';
	single["'"] = 'ь';

	single['A'] = 'А';
	single['B'] = 'Б';
	single['V'] = 'В';
	single['G'] = 'Г';
	single['D'] = 'Д';
	single['E'] = 'Е';
	single['Z'] = 'З';
	single['I'] = 'И';
	single['K'] = 'К';
	single['L'] = 'Л';
	single['M'] = 'М';
	single['N'] = 'Н';
	single['O'] = 'О';
	single['P'] = 'П';
	single['R'] = 'Р';
	single['S'] = 'С';
	single['T'] = 'Т';
	single['U'] = 'У';
	single['F'] = 'Ф';
	single['C'] = 'Ц';
	single['Y'] = 'Ы';

	j['o'] = 'ё';
	j['j'] = 'й';
	j['u'] = 'ю';
	j['a'] = 'я';

	j['O'] = 'Ё';
	j['J'] = 'Й';
	j['U'] = 'Ю';
	j['A'] = 'Я';

	h['z'] = 'ж';
	h['k'] = 'х';
	h['c'] = 'ч';
	h['s'] = 'ш';
	h['sh'] = 'щ';
	h['e'] = 'э';

	h['Z'] = 'Ж';
	h['K'] = 'Х';
	h['C'] = 'Ч';
	h['S'] = 'Ш';
	h['SH'] = 'Щ';
	h['E'] = 'Э';

	/*
	Таблицы ГОСТ-а 16876-71

	Буквы j и h используются в качестве модификаторов, причем j ставится перед основной буквой, а h - после основной буквы.

	 а - a       к - k       х - kh
	 б - b       л - l       ц - c
	 в - v       м - m       ч - ch
	 г - g       н - n       ш - sh
	 д - d       о - o       щ - shh
	 е - e       п - p       ъ - "
	 ё - jo      р - r       ы - y
	 ж - zh      с - s       ь - '
	 з - z       т - t       э - eh
	 и - i       у - u       ю - ju
	 й - jj      ф - f       я - ja
	*/

	function findA(c)
	{
		for (i=0; i < a.length; i++)
			if (a[i] == c) return i;
		return -1;
	}

	function transliterateText(src)
	{

		var res = '';

		for (i=0; i < src.length; i++)
		{
			c = src.charAt(i);
			c1 = src.charAt(i+1);
			if (c == 'j' && j[c1] != null)
			{
				res += j[c1];
				i++;
			}
			else if (c1 == 'h')
			{
				if (c == 's' && c1=='h' && src.charAt(i+2) == 'h')
				{
					res += h['sh'];
					i += 2;
				}
				else
				{
					if (h[c] != null)
						res += h[c];
					else
						res +=c;
					i++;
				}
			}
			else if (single[c] != null && single[c] != null)
			{
				res += single[c];
			}
			else
			{
				res += c;
			}       	
		}
		//alert(res);
			
		return res;
	}