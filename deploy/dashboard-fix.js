// Define questions array for route calculations
const questions = [
    {
        q: "1. ¿Qué actividad te resulta más atractiva?",
        options: [
            { text: "Crear contenido visual o escribir para redes", routes: ["Diseño de contenido", "Marketing en redes"] },
            { text: "Resolver problemas con lógica o códigos", routes: ["Programación", "Desarrollo de software"] },
            { text: "Pensar en nuevas ideas para negocios", routes: ["Emprendimiento", "Creatividad e innovación"] },
            { text: "Ayudar a los demás o resolver consultas", routes: ["Servicio al cliente", "Ventas"] }
        ]
    },
    {
        q: "2. ¿Qué preferís aprender?",
        options: [
            { text: "Herramientas como Excel, Word, PowerPoint", routes: ["Competencias digitales"] },
            { text: "Cómo gestionar recursos y tareas", routes: ["Manejo de proyectos"] },
            { text: "Cómo proteger la información en internet", routes: ["Ciberseguridad"] },
            { text: "Cómo tomar decisiones con números", routes: ["Contabilidad", "Análisis de datos"] }
        ]
    },
    {
        q: "3. ¿Te gustaría trabajar más con...?",
        options: [
            { text: "Gente, clientes o audiencias", routes: ["Servicio al cliente", "Marketing", "Ventas"] },
            { text: "Computadoras y tecnología", routes: ["IA", "Software", "Ciberseguridad"] },
            { text: "Ideas nuevas y creatividad", routes: ["Creatividad", "Diseño", "Emprendimiento"] },
            { text: "Organización y procesos", routes: ["Transformación digital", "Proyectos", "Contabilidad"] }
        ]
    },
    {
        q: "4. Si tenés que hacer un proyecto escolar, ¿qué rol preferís?",
        options: [
            { text: "Diseñar la presentación o editar el video", routes: ["Diseño de contenido"] },
            { text: "Coordinar al equipo y definir tareas", routes: ["Manejo de proyectos"] },
            { text: "Analizar datos o gráficos", routes: ["Análisis de datos", "Contabilidad"] },
            { text: "Investigar o buscar soluciones técnicas", routes: ["IA", "Software", "Ciberseguridad"] }
        ]
    },
    {
        q: "5. ¿Cuál de estas frases se parece más a vos?",
        options: [
            { text: "Tengo buena imaginación y muchas ideas", routes: ["Creatividad e innovación"] },
            { text: "Soy práctico y resuelvo problemas técnicos", routes: ["Programación", "Análisis de datos"] },
            { text: "Me gusta atender y entender a las personas", routes: ["Servicio al cliente", "Ventas"] },
            { text: "Soy ordenado/a y me fijo en los detalles", routes: ["Contabilidad", "Proyectos"] }
        ]
    },
    {
        q: "6. ¿Qué tipo de contenido te interesa ver o crear?",
        options: [
            { text: "Videos, memes, campañas", routes: ["Diseño de contenido", "Marketing"] },
            { text: "Juegos, apps, herramientas", routes: ["Desarrollo de software", "Videojuegos"] },
            { text: "Planes de negocios o estrategias", routes: ["Emprendimiento", "Transformación digital"] },
            { text: "Cursos, hojas de cálculo, documentos", routes: ["Competencias digitales", "Ofimática"] }
        ]
    },
    {
        q: "7. ¿Qué te gustaría saber hacer en un futuro cercano?",
        options: [
            { text: "Hacer crecer un emprendimiento", routes: ["Emprendimiento"] },
            { text: "Crear un videojuego o una app", routes: ["Videojuegos", "Software"] },
            { text: "Comunicar bien un producto o marca", routes: ["Marketing", "Ventas"] },
            { text: "Usar tecnología para resolver tareas", routes: ["Ofimática", "Transformación digital"] }
        ]
    },
    {
        q: "8. ¿Cuál de estas áreas te llama más la atención?",
        options: [
            { text: "Inteligencia Artificial o aprendizaje automático", routes: ["IA"] },
            { text: "Finanzas personales o registro de gastos", routes: ["Contabilidad"] },
            { text: "Herramientas para trabajar mejor en equipo", routes: ["Manejo de proyectos"] },
            { text: "Redes sociales y campañas digitales", routes: ["Marketing en redes"] }
        ]
    },
    {
        q: "9. ¿Qué elegís si tenés 2 horas libres para aprender algo nuevo?",
        options: [
            { text: "Tomar un curso creativo o de diseño", routes: ["Diseño de contenido"] },
            { text: "Investigar sobre cómo funciona un sistema", routes: ["Software", "IA"] },
            { text: "Aprender técnicas de ventas o atención al cliente", routes: ["Ventas", "Servicio al cliente"] },
            { text: "Organizar información o datos", routes: ["Análisis de datos", "Competencias digitales"] }
        ]
    },
    {
        q: "10. ¿Qué frase te representa mejor?",
        options: [
            { text: "Tengo buena imaginación y muchas ideas", routes: ["Creatividad", "IA"] },
            { text: "Soy práctico y resuelvo problemas técnicos", routes: ["Programación", "Análisis de datos"] },
            { text: "Me gusta atender y entender a las personas", routes: ["Servicio al cliente", "Ventas"] },
            { text: "Soy ordenado/a y me fijo en los detalles", routes: ["Contabilidad", "Proyectos"] }
        ]
    },
    {
        q: "11. ¿Cuál de estas actividades elegirías en una feria de profesiones?",
        options: [
            { text: "Hacer una app o juego", routes: ["Desarrollo de software", "Videojuegos"] },
            { text: "Vender un producto con una estrategia", routes: ["Ventas", "Marketing"] },
            { text: "Hacer una campaña visual", routes: ["Diseño de contenido"] },
            { text: "Armar un plan para un nuevo negocio", routes: ["Emprendimiento"] }
        ]
    },
    {
        q: "12. ¿Cuál sería tu "súper poder" ideal?",
        options: [
            { text: "Entender el lenguaje de las máquinas", routes: ["Programación", "IA"] },
            { text: "Hacer que la gente diga "wow" con una presentación", routes: ["Creatividad", "Diseño de contenido"] },
            { text: "Convencer a cualquiera de comprar algo", routes: ["Ventas", "Marketing"] },
            { text: "Organizarlo todo para que funcione perfecto", routes: ["Proyectos", "Contabilidad"] }
        ]
    }
];
