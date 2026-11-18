(function(){

    var form = document.getElementById("formdatos");

function soloLetras(texto) {
    return /^[\p{L}\s]+$/u.test(texto);
}

    function soloNumeros(texto){
        return /^[0-9]+$/.test(texto);
    }

    var validar = function(e){

        var errores = [];

        /* =========================
           DATOS DEL ALUMNO
        ==========================*/

        var nombre = form.nombre_alum.value.trim();
        if(nombre === ""){
            errores.push("• Nombre del alumno vacío");
        } else if(!soloLetras(nombre)){
            errores.push("• El nombre del alumno no debe contener números");
        }

        var ape_pat = form.ape_pat.value.trim();
        if(ape_pat === ""){
            errores.push("• Apellido paterno vacío");
        } else if(!soloLetras(ape_pat)){
            errores.push("• El apellido paterno no debe contener números");
        }

        var ape_mat = form.ape_mat.value.trim();
        if(ape_mat === ""){
            errores.push("• Apellido materno vacío");
        } else if(!soloLetras(ape_mat)){
            errores.push("• El apellido materno no debe contener números");
        }

        var curp = form.curp_alum.value.trim().replace(/\s+/g,"");
        form.curp_alum.value = curp;

        if(curp === ""){
            errores.push("• CURP del alumno vacío");
        } else if(curp.length !== 18){
            errores.push("• El CURP del alumno debe tener 18 caracteres");
        } else {
            var regexCurp = /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i;
            if(!regexCurp.test(curp)){
                errores.push("• CURP inválido");
            }
        }

        if(form.sexo.value === ""){
            errores.push("• Seleccione sexo del alumno");
        }

        if(form.nacimiento_alum.value === ""){
            errores.push("• Fecha de nacimiento del alumno vacía");
        }

        if(form.grado.value === ""){
            errores.push("• Seleccione grado");
        }

        if(form.grupo.value === ""){
            errores.push("• Seleccione grupo");
        }

        if(form.muni_alum_nac.value.trim() === ""){
            errores.push("• Municipio de nacimiento vacío");
        }

        if(form.estado_alum_nac.value === ""){
            errores.push("• Seleccione estado de nacimiento");
        }

        if(form.escuela_proce.value.trim() === ""){
            errores.push("• Escuela de procedencia vacía");
        }

        /* =========================
           DOMICILIO
        ==========================*/

        if(form.calle_alum.value.trim() === ""){
            errores.push("• Calle del alumno vacía");
        }

        if(form.colo_alum.value.trim() === ""){
            errores.push("• Colonia del alumno vacía");
        }

        if(form.muni_alum_rad.value.trim() === ""){
            errores.push("• Municipio de radicación vacío");
        }

        if(form.estado_alum_rad.value === ""){
            errores.push("• Seleccione estado de radicación del alumno");
        }

        var cp = form.cp.value.trim();
        if(cp === ""){
            errores.push("• Código postal vacío");
        } else if(!soloNumeros(cp)){
            errores.push("• El código postal solo debe contener números");
        }

        var telCasa = form.tel_casa_alum.value.trim();
        if(telCasa === ""){
            errores.push("• Teléfono de casa vacío");
        }else if(telCasa.length !== 10){
            errores.push("• El numero de casa del alumno debe tener 10 digitos");
        }else if(!soloNumeros(telCasa)){
            errores.push("• Teléfono de casa solo debe contener números");
        }

        var telCel = form.tel_cel_alum.value.trim();
        if(telCel === ""){
            errores.push("• Teléfono celular vacío");
        } else if(telCel.length !== 10){
            errores.push("• El numero celular del alumno debe tener 10 digitos");
        } else if(!soloNumeros(telCel)){
            errores.push("• Teléfono celular solo debe contener números");
        }

        var peso = form.peso.value.trim();
        if(peso === ""){
            errores.push("• Peso vacío");
        } else if(!soloNumeros(peso)){
            errores.push("• Peso solo debe contener números");
        }

        var talla = form.talla.value.trim();
        if(talla === ""){
            errores.push("• Altura vacía");
        } else if(!soloNumeros(talla)){
            errores.push("• Altura solo debe contener números");
        }

        /* =========================
           SALUD
        ==========================*/

        if(form.alergias.value.trim() === ""){
            errores.push("• Campo alergias vacío (coloque NA si no aplica)");
        }

        if(form.sangre.value === ""){
            errores.push("• Seleccione tipo de sangre");
        }

        if(form.enfermedades.value.trim() === ""){
            errores.push("• Campo enfermedades vacío (coloque NA si no aplica)");
        }

        if(form.religion.value.trim() === ""){
            errores.push("• Campo religión vacío");
        }

        /* =========================
           Validar que una persona llene sus datos
        ==========================*/

        var curp_papa = form.curp_papa.value.trim();
        var nombre_papa = form.nombre_papa.value.trim();
        var nacimiento_papa = form.nacimiento_papa.value.trim();
        var estado_papa =form.estado_papa.value;
        var calle_papa = form.calle_papa.value.trim();
        var colonia_papa = form.colonia_papa.value.trim();
        var estado_civ_papa = form.estado_civ_papa.value;
        var ocupacion_papa = form.ocupacion_papa.value.trim();
        var escolaridad_papa = form.escolaridad_papa.value.trim();
        var tel_casa_papa = form.tel_casa_papa.value.trim();
        var email_papa =form.email_papa.value.trim();

        var curp_mama = form.curp_mama.value.trim();
        var nombre_mama = form.nombre_mama.value.trim();
        var nacimiento_mama = form.nacimiento_mama.value.trim();
        var estado_mama =form.estado_mama.value;
        var calle_mama = form.calle_mama.value.trim();
        var colonia_mama = form.colo_mama.value.trim();
        var civil_mama = form.civil_mama.value;
        var ocupacion_mama = form.ocupacion_mama.value.trim();
        var esco_mama = form.esco_mama.value.trim();
        var tel_casa_mama = form.tel_casa_mama.value.trim();
        var email_mama =form.email_mama.value.trim();

        var curp_tutor = form.curp_tutor.value.trim();
        var nombre_tutor = form.nombre_tutor.value.trim();
        var nacimiento_tutor = form.nacimiento_tutor.value.trim();
        var estado_tutor =form.estado_tutor.value;
        var calle_tutor = form.calle_tutor.value.trim();
        var colonia_tutor = form.colo_tutor.value.trim();
        var civil_tutor = form.estado_civ_tutor.value;
        var ocupacion_tutor = form.ocupacion_tutor.value.trim();
        var esco_tutor = form.esco_tutor.value.trim();
        var tel_casa_tutor = form.tel_casa_tutor.value.trim();
        var email_tutor =form.email_tutor.value.trim();


                const papaVacio = (
                    curp_papa === "" &&
                    nombre_papa === "" &&
                    nacimiento_papa === "" &&
                    estado_papa === "" &&
                    calle_papa === "" &&
                    colonia_papa === "" &&
                    estado_civ_papa === "" &&
                    ocupacion_papa === "" &&
                    escolaridad_papa === "" &&
                    tel_casa_papa === "" &&
                    email_papa === ""
                );

                const mamaVacio = (
                    curp_mama === "" &&
                    nombre_mama === "" &&
                    nacimiento_mama === "" &&
                    estado_mama === "" &&
                    calle_mama === "" &&
                    colonia_mama === "" &&
                    civil_mama === "" &&
                    ocupacion_mama === "" &&
                    esco_mama === "" &&
                    tel_casa_mama === "" &&
                    email_mama === ""
                );

                const tutorVacio = (
                    curp_tutor === "" &&
                    nombre_tutor === "" &&
                    nacimiento_tutor === "" &&
                    estado_tutor === "" &&
                    calle_tutor === "" &&
                    colonia_tutor === "" &&
                    civil_tutor === "" &&
                    ocupacion_tutor === "" &&
                    esco_tutor === "" &&
                    tel_casa_tutor === "" &&
                    email_tutor === ""
                );

                if (papaVacio && mamaVacio && tutorVacio) {
                    errores.push("• Debe capturar los datos al menos de una persona: padre, madre o tutor");
                }


        /* =========================
           DATOS DEL PADRE
        ==========================*/

        var curp_papa = form.curp_papa.value.trim();
        var nombre_papa = form.nombre_papa.value.trim();
        var nacimiento_papa = form.nacimiento_papa.value.trim();
        var estado_papa =form.estado_papa.value;
        var calle_papa = form.calle_papa.value.trim();
        var colonia_papa = form.colonia_papa.value.trim();
        var estado_civ_papa = form.estado_civ_papa.value;
        var ocupacion_papa = form.ocupacion_papa.value.trim();
        var escolaridad_papa = form.escolaridad_papa.value.trim();
        var tel_casa_papa = form.tel_casa_papa.value.trim();
        var email_papa =form.email_papa.value.trim();


        if(curp_papa !== "" || nombre_papa !== "" || nacimiento_papa !== "" || 
            estado_papa !== "" || calle_papa !== "" || colonia_papa !== "" || 
            estado_civ_papa !== "" || ocupacion_papa !== "" || escolaridad_papa !== "" || 
            tel_casa_papa !== "" || email_papa !== "" ){
            
            if(curp_papa === "" || nombre_papa === "" || nacimiento_papa === "" || 
            estado_papa === "" || calle_papa === "" || colonia_papa === "" || 
            estado_civ_papa === "" || ocupacion_papa === "" || escolaridad_papa === "" || 
            tel_casa_papa === "" || email_papa === "" 
            ){
                errores.push("• Si captura datos del padre debe llenar CURP, nombre, fecha de nacimiento, Lugar de nacimiento, Calle y numero, colonia, estado civil, ocupacion, escolaridad, telefono y correo");
            }else if(curp_papa.length !== 18){
            errores.push("• El CURP del padre debe tener 18 caracteres");
        } else {
            var regexCurp = /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i;
            if(!regexCurp.test(curp_papa)){
                errores.push("• CURP inválido");
            }
        }
        }

        /* =========================
           DATOS DE LA MADRE
        ==========================*/

        var curp_mama = form.curp_mama.value.trim();
        var nombre_mama = form.nombre_mama.value.trim();
        var nacimiento_mama = form.nacimiento_mama.value.trim();
        var estado_mama =form.estado_mama.value;
        var calle_mama = form.calle_mama.value.trim();
        var colonia_mama = form.colo_mama.value.trim();
        var civil_mama = form.civil_mama.value;
        var ocupacion_mama = form.ocupacion_mama.value.trim();
        var esco_mama = form.esco_mama.value.trim();
        var tel_casa_mama = form.tel_casa_mama.value.trim();
        var email_mama =form.email_mama.value.trim();


        if(curp_mama !== "" || nombre_mama !== "" || nacimiento_mama !== "" || 
            estado_mama !== "" || calle_mama !== "" || colonia_mama !== "" || 
            civil_mama !== "" || ocupacion_mama !== "" || esco_mama !== "" || 
            tel_casa_mama !== "" || email_mama !== "" ){
            
            if(curp_mama === "" || nombre_mama === "" || nacimiento_mama === "" || 
            estado_mama === "" || calle_mama === "" || colonia_mama === "" || 
            civil_mama === "" || ocupacion_mama === "" || esco_mama === "" || 
            tel_casa_mama === "" || email_mama === "" 
            ){
                errores.push("• Si captura datos de la madre debe llenar CURP, nombre, fecha de nacimiento, Lugar de nacimiento, Calle y numero, colonia, estado civil, ocupacion, escolaridad, telefono y correo");
            }else if(curp_mama.length !== 18){
            errores.push("• El CURP del padre debe tener 18 caracteres");
        } else {
            var regexCurp = /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i;
            if(!regexCurp.test(curp_mama)){
                errores.push("• CURP inválido");
            }
        }
        }



        /* =========================
           DATOS DEL TUTOR
        ==========================*/

        var curp_tutor = form.curp_tutor.value.trim();
        var nombre_tutor = form.nombre_tutor.value.trim();
        var nacimiento_tutor = form.nacimiento_tutor.value.trim();
        var estado_tutor =form.estado_tutor.value;
        var calle_tutor = form.calle_tutor.value.trim();
        var colonia_tutor = form.colo_tutor.value.trim();
        var civil_tutor = form.estado_civ_tutor.value;
        var ocupacion_tutor = form.ocupacion_tutor.value.trim();
        var esco_tutor = form.esco_tutor.value.trim();
        var tel_casa_tutor = form.tel_casa_tutor.value.trim();
        var email_tutor =form.email_tutor.value.trim();


        if(curp_tutor !== "" || nombre_tutor !== "" || nacimiento_tutor !== "" || 
            estado_tutor !== "" || calle_tutor !== "" || colonia_tutor !== "" || 
            civil_tutor !== "" || ocupacion_tutor !== "" || esco_tutor !== "" || 
            tel_casa_tutor !== "" || email_tutor !== "" ){
            
            if(curp_tutor === "" || nombre_tutor === "" || nacimiento_tutor === "" || 
            estado_tutor === "" || calle_tutor === "" || colonia_tutor === "" || 
            civil_tutor === "" || ocupacion_tutor === "" || esco_tutor === "" || 
            tel_casa_tutor === "" || email_tutor === "" 
            ){
                errores.push("• Si captura datos de la tutor debe llenar CURP, nombre, fecha de nacimiento, Lugar de nacimiento, Calle y numero, colonia, estado civil, ocupacion, escolaridad, telefono y correo");
            }else if(curp_tutor.length !== 18){
            errores.push("• El CURP del padre debe tener 18 caracteres");
        } else {
            var regexCurp = /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i;
            if(!regexCurp.test(curp_tutor)){
                errores.push("• CURP inválido");
            }
        }
        }

        /* =========================
           MOSTRAR ERRORES
        ==========================*/

        if(errores.length > 0){
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Se encontraron errores',
                html: errores.join("<br>"),
                confirmButtonText: 'Aceptar'
            });
        }

    };

    form.addEventListener("submit", validar);

}());
