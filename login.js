function loguear()
{


let nombre=document.getElementById("nombre").value;

let contraseña=document.getElementById("contrasena").value;

if (nombre=="Josting" && contraseña=="12345") 
{
    window.location="login.htm"

}
else
{
    alert("Dstos Incorrectos");
}
}