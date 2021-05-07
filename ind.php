<?
define('VUEJS_DEBAG', true);
AddEventHandler("main", "OnEpilog", "OnEpilogHandler");
function OnEpilogHandler()
{
    global $APPLICATION;
    if (!defined('ERROR_404') && intval($_GET["PAGEN_1"]) > 0) {
        $APPLICATION->SetPageProperty("title", $APPLICATION->getTitle(false) . " - Page " . intval($_GET["PAGEN_1"]));
    }
}

AddEventHandler("main", "OnEndBufferContent", "removeType");
function removeType(&$content)
{
	$content = replace_output($content);
}
function replace_output($d)
{
	return str_replace(' type="text/javascript"', "", $d);
}

function getUserRole($user_id) {
    $user_groups = CUser::GetUserGroup($user_id);
    
    return (in_array(5, $user_groups)) ? "director" : ((in_array(7, $user_groups)) ? "findir" : "engineer");
}

function UserBallsPM($vUserBall = 0, $vAction)
{

	global $DB, $USER_FIELD_MANAGER, $USER;

	$rsUser = CUser::GetByID($USER->GetID());
	$arUser = $rsUser->Fetch();
	$arUserId = $arUser['ID'];
	$arUserBall = $arUser['UF_BALL'];

	if ($vAction == 'p') {
		$fields = Array(
		"UF_BALL" => $arUserBall + $vUserBall
		);
	} else if ($vAction == 'm')  {
		$fields = Array(
		"UF_BALL" => $arUserBall - $vUserBall
		);	
	} else {
		$fields = Array(
		"UF_BALL" => $arUserBall
		);		
	
	}

	return $USER_FIELD_MANAGER->Update("USER", $arUserId, $fields);

}

function UserConSpor($vElId)
{

	global $DB, $USER_FIELD_MANAGER, $USER;

	$rsUser = CUser::GetByID($USER->GetID());
	$arUser = $rsUser->Fetch();
	$arUserId = $arUser['ID'];

	$ELEMENT_ID = $vElId;  // код элемента
	$PROPERTY_CODE = "LOGIN2_SPOR";  // код свойства
	$PROPERTY_VALUE = $arUserId ;  // значение свойства

// Установим новое значение для данного свойства данного элемента
//CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, false, array($PROPERTY_CODE => $PROPERTY_VALUE));

	return CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, 46, array($PROPERTY_CODE => $PROPERTY_VALUE));

}

function addUserFr($vUserId)
{

	global $DB, $USER_FIELD_MANAGER, $USER;

	$rsUser = CUser::GetByID($USER->GetID());
	$arUser = $rsUser->Fetch();
	$arUserId = $arUser['ID'];
	$UserId = $vUserId;

	if ($arUser['UF_PODPISKA']) {
		$fields = Array(
			"UF_PODPISKA" => $arUser['UF_PODPISKA'] . ',' . $arUserId
		);
	} else {
		$fields = Array(
			"UF_PODPISKA" => $arUserId
		);
	}	
		
			


	return $USER_FIELD_MANAGER->Update("USER", $UserId, $fields);

}
function delUserFr($vUserId)
{

	global $DB, $USER_FIELD_MANAGER, $USER;

	$rsUser = CUser::GetByID($USER->GetID());
	$arUser = $rsUser->Fetch();
	$arUserId = $arUser['ID'];
	$UserId = $vUserId;


	$podpiska = explode(",", $arUser['UF_PODPISKA']);

	$key = array_search($UserId, $podpiska);
	if ($key !== false)
	{
		unset($podpiska[$key]);
	}	
	
	$podpiska2 = implode(",", $podpiska);
	

	$fields = Array(
		"UF_PODPISKA" => $podpiska2
	);
	
		
			


	return $USER_FIELD_MANAGER->Update("USER", $arUserId, $fields);

}


AddEventHandler("main", "OnAfterUserRegister", Array("MyClass", "OnAfterUserRegisterHandler"));
class MyClass
{
	function OnAfterUserRegisterHandler(&$arFields)
	{
		if($arFields["USER_ID"]>0)
		{
			if(SITE_ID=="s4")
			{
			    // Создаем счет пользователю при регистрации
				global $USER;
				if(!CSaleUserAccount::GetByUserID($USER->GetID(), "RUB")){
				   $arFields = Array("USER_ID" => $USER->GetID(), "CURRENCY" => "RUB", "CURRENT_BUDGET" => 0);
				   CSaleUserAccount::Add($arFields);  
				}
				// Добавляем 1000 баллов пользователю при регистрации 
				UserBallsPM(1000, 'p');
			}

		}
		return $arFields;
	}
}

?>