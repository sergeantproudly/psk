function checkNotEmpty(fld){

	if(fld.value)return true;

	else{

		$(fld).addClass('err');

		return false;

	}

}

function checkEmail(fld){

	var reg=/^[a-zA-Z0-9_\.\-]+@([a-zA-Z0-9][a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/;

	var str=fld.value;

	if(!str || reg.test(str))return true;

	else{

		$(fld).addClass('err');

		return false;

	}

}

function checkPhone(fld){

	var reg=/[0-9\+\-() ,\.]+/;

	var str=fld.value;

	if(!str || reg.test(str))return true;

	else{

		$(fld).addClass('err');

		return false;

	}

}

function checkDate(fld){

	var reg=/^(\d){1,2}\.(\d){1,2}\.(\d){4}$/;

	var str=fld.value;

	if(x!='')return reg.test(x);

	if(!str || reg.test(str))return true;

	else{

		$(fld).addClass('err');

		return false;

	}

}

function checkResetStatus(elem){

	if(elem.tagName.toLowerCase()!='form'){

		$(elem).removeClass('err');

	}else{

		$(elem).find('input, textarea').each(function(index,input){

			checkResetStatus(input);

		});

	}

}

function checkPassword(fld){

	var reg=/[A-z0-9\.]+/;

	var str=fld.value;

	if(reg.test(str))return true;

	else{

		$(fld).addClass('err');

		return false;

	}

}

function checkTheSame(fld){

	var fld2=$('#'+fld.id+'_repeat').get(0);

	if(fld.value==fld2.value)return true;

	else{

		$(fld).addClass('err');

		return false;

	}
}

function checkElements(elements, patterns) {
	var correct = true;
	for(var i = 0; i < elements.length; i++) {
		if (patterns[i][1] && !checkNotEmpty(elements[i])) {
			correct=false;

		} else {
			if (patterns[i][2] && !checkEmail(elements[i])) {
				correct=false;
			}
			if (patterns[i][3] && !checkPhone(elements[i])) {
				correct=false;
			}
			if (patterns[i][4] && !checkDate(elements[i])) {
				correct=false;
			}
			if (patterns[i][5] && !checkPassword(elements[i])) {
				correct=false;
			}
			if (patterns[i][6] && !checkTheSame(elements[i])) {
				correct=false;
			}
		}
	}
	return correct;
}