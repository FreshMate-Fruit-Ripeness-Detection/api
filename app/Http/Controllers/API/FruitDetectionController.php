<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Contracts\FruitDetectionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Fruit Detection API Documentation",
 *     description="API for detecting fruit ripeness using machine learning",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * ),
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class FruitDetectionController extends Controller
{
    private $fruitDetectionService;

    public function __construct(FruitDetectionServiceInterface $fruitDetectionService)
    {
        $this->fruitDetectionService = $fruitDetectionService;
    }

    /**
     * @OA\Post(
     *     path="/api/detect-ripeness",
     *     tags={"Fruit Detection"},
     *     summary="Detect fruit ripeness from image",
     *     description="Upload an image of a fruit to detect its ripeness",
     *     operationId="detectRipeness",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="The fruit image to analyze"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful detection",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="fruit_type", type="string", example="apple"),
     *             @OA\Property(property="ripeness", type="string", example="ripe"),
     *             @OA\Property(property="confidence", type="number", format="float", example=0.95),
     *             @OA\Property(property="timestamp", type="string", format="datetime", example="2024-11-28T12:00:00Z"),
     *             @OA\Property(property="image_path", type="string", example="/storage/scans/image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or processing error"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function detectRipeness(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $result = $this->fruitDetectionService->detectRipeness($request->file('image'));

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('Detection API failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/diseases-fruit",
     *     tags={"Fruit Detection"},
     *     summary="Get detection history",
     *     description="Retrieve the history of fruit detections",
     *     operationId="getDetectionHistory",
     *     @OA\Response(
     *         response=200,
     *         description="List of previous detections",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="diseases_name", type="string"),
     *                     @OA\Property(property="diseases_desc", type="string"),
     *                     @OA\Property(property="dieases_preview", type="string"),
     *                     @OA\Property(property="diseases_detail", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getHistory()
    {
        try {
            $data = [
                [
                "id" => 1,
                "fruit_name"=>"Apel",
                "fruit_desc"=>"Apel adalah buah yang manis dan renyah, sangat bergizi, serta banyak dikonsumsi di seluruh dunia. Apel merupakan sumber serat makanan yang baik untuk mendukung kesehatan pencernaan, serta mengandung vitamin C yang meningkatkan kekebalan tubuh. Apel rendah kalori dan mengandung antioksidan yang mendukung kesehatan jantung serta membantu mengurangi risiko penyakit kronis. Dengan varietas seperti Fuji, Granny Smith, dan Honeycrisp, apel sangat serbaguna dan dapat dinikmati dalam keadaan segar, dipanggang, atau dijus.",
                "fruit_image_preview"=>"https://png.pngtree.com/png-clipart/20231018/original/pngtree-fresh-apple-fruit-red-png-image_13344485.png",
                "fruit_image_detail"=>"https://5.imimg.com/data5/SELLER/Default/2022/12/YQ/GO/YG/122256352/split-dry-khada-dhaniya-coriander-.jpg"
              ],
            [
                "id"=>2,
                "fruit_name"=>"Pisang",
                "fruit_desc"=>"Pisang adalah buah yang lembut dan manis, kaya akan nutrisi penting seperti kalium, vitamin B6, dan vitamin C. Pisang sangat baik untuk menjaga tingkat energi, mendukung kesehatan jantung, dan membantu fungsi otot. Kandungan gula alaminya menjadikannya camilan cepat yang sempurna, sementara seratnya yang tinggi membantu pencernaan. Pisang juga sering digunakan dalam smoothie, kue, dan hidangan penutup.",
                "fruit_image_preview"=>"https://media.istockphoto.com/id/1194873081/photo/fresh-whole-half-and-sliced-banana.jpg?s=612x612&w=0&k=20&c=FxnmPX0kDbNCexSOsQ1K-lS7w27Vdm0uzM_TAFo3rsY=",
                "fruit_image_detail"=>"https://www.foodandwine.com/thmb/4fzQW9u60XlhTk52CIuM1BlLhcc=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/amazonfreebananas-em-86304874-2000-5a276309cf1944349fb55818c98c7b1b.jpg"
              ],
            [
                "id"=>3,
                "fruit_name"=>"Jeruk",
                "fruit_desc"=>"Jeruk adalah buah sitrus yang berair, dikenal karena rasa asam-manisnya dan kandungan vitamin C yang tinggi. Jeruk juga mengandung kalium, folat, dan antioksidan yang penting untuk menjaga kesehatan kulit, meningkatkan kekebalan tubuh, dan mendukung kesehatan jantung. Jeruk umumnya dikonsumsi dalam keadaan segar, dijus, atau digunakan sebagai penambah rasa pada hidangan penutup dan masakan gurih.",
                "fruit_image_preview"=>"https://i.pinimg.com/736x/4d/ca/04/4dca04e7f7065e304502e2eb353fcc5e.jpg",
                "fruit_image_detail"=>"https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Orange-Fruit-Pieces.jpg/2560px-Orange-Fruit-Pieces.jpg"
              ],
            [
                "id"=>4,
                "fruit_name"=>"Anggur",
                "fruit_desc"=>"Anggur adalah buah kecil yang manis dan tersedia dalam varietas hijau, merah, atau hitam. Anggur kaya akan vitamin C dan K, serta mengandung antioksidan kuat seperti resveratrol, yang mendukung kesehatan jantung dan dapat membantu mengurangi peradangan. Anggur sering dikonsumsi dalam keadaan segar, dikeringkan menjadi kismis, atau digunakan untuk membuat anggur dan jus. Anggur merupakan camilan yang sempurna dan bahan yang sangat baik untuk salad serta hidangan penutup.",
                "fruit_image_preview"=>"https://png.pngtree.com/png-vector/20240628/ourlarge/pngtree-grape-fruit-top-view-raison-png-image_12726127.png",
                "fruit_image_detail"=>"https://cdn.pixabay.com/photo/2023/10/10/16/05/grapes-8306833_1280.jpg"
              ],
            [
                "id"=>5,
                "fruit_name"=>"Jambu",
                "fruit_desc"=>"Jambu adalah buah tropis dengan rasa manis dan asam serta interior yang cerah. Jambu adalah sumber vitamin C yang luar biasa, mengandung empat kali lebih banyak daripada jeruk. Jambu juga kaya akan serat, kalium, dan antioksidan, menjadikannya sangat baik untuk meningkatkan kekebalan tubuh, memperbaiki pencernaan, dan mendukung kesehatan jantung. Jambu dapat dikonsumsi dalam keadaan segar, dijus, atau digunakan untuk membuat selai dan hidangan penutup.",
                "fruit_image_preview"=>"https://static.vecteezy.com/system/resources/previews/040/749/089/non_2x/ai-generated-guava-fruit-guava-isolated-tropical-fruit-guava-top-view-guava-flat-lay-png.png",
                "fruit_image_detail"=>"https://media.istockphoto.com/id/1309882751/photo/guava-with-slice-isolated-on-white-background-clipping-path-top-view.jpg?s=612x612&w=0&k=20&c=eQuhBe_RKQ-jfT0b8dtamXrInQvyU4zcgQIa2-WWC2Y="
              ],
            [
                "id"=>6,
                "fruit_name"=>"Jujube",
                "fruit_desc"=>"Jujube, yang juga dikenal sebagai kurma merah, adalah buah kecil yang manis dengan tekstur kenyal. Buah ini kaya akan vitamin C, antioksidan, dan mineral seperti kalium dan besi. Jujube dikenal karena sifatnya yang menenangkan dan sering digunakan dalam pengobatan tradisional untuk meningkatkan tidur dan meningkatkan kekebalan tubuh. Buah ini dapat dikonsumsi dalam keadaan segar, dikeringkan, atau digunakan dalam teh dan hidangan penutup.",
                "fruit_image_preview" => "https://static.vecteezy.com/system/resources/previews/050/247/373/non_2x/jujube-fruit-isolated-on-transparent-background-png.png",
                "fruit_image_detail" =>"https://theearthyfoods.com/cdn/shop/products/DSC07054.jpg?v=1654106567&width=1946"
              ],
            [
                "id"=>7,
                "fruit_name"=>"Delima",
                "fruit_desc"=>"Delima adalah buah unik yang berisi biji berwarna merah delima yang berair, yang disebut arils. Delima kaya akan antioksidan, terutama punicalagins, dan memberikan jumlah yang signifikan dari vitamin C, vitamin K, dan serat. Delima mendukung kesehatan jantung, memperbaiki pencernaan, dan dapat memiliki sifat anti-inflamasi. Buah ini dinikmati dalam keadaan segar, dijus, atau sebagai bahan dalam salad dan hidangan penutup.",
                "fruit_image_preview"=>"https://sesa.id/cdn/shop/products/delimaimport2-removebg-preview.png?v=1677578552",
                "fruit_image_detail"=>"https://as1.ftcdn.net/v2/jpg/04/32/47/36/1000_F_432473688_NBFaMH9L7Ls0kvAxnCZnRlvbCaSgxozB.jpg"
              ],
            [
                "id"=>8,
                "fruit_name"=>"Stroberi",
                "fruit_desc"=>"Stroberi adalah buah beri berwarna merah cerah dan berair, dikenal karena rasa manisnya dan nilai gizi yang tinggi. Stroberi merupakan sumber yang sangat baik dari vitamin C, mangan, dan antioksidan, yang mendukung kesehatan kulit, meningkatkan kekebalan tubuh, dan mengurangi peradangan. Stroberi serbaguna dan dapat dinikmati dalam keadaan segar, dicampur dalam smoothie, atau digunakan dalam hidangan penutup seperti kue dan selai.",
                "fruit_image_preview"=>"https://static.vecteezy.com/system/resources/previews/015/100/113/non_2x/strawberry-transparent-background-free-png.png",
                "fruit_image_detail"=>"https://mhomecare.co.id/blog/wp-content/uploads/2021/10/Manfaat-strawberry-untuk-kesehatan-min.jpg"
              ],
            [
                "id"=>9,
                "fruit_name"=>"Nanas",
                "fruit_desc"=>"Nanas adalah buah tropis dengan rasa asam-manis dan daging berwarna kuning cerah. Nanas kaya akan vitamin C, mangan, dan bromelain, enzim yang dikenal untuk membantu pencernaan dan mengurangi peradangan. Nanas menyegarkan dan menghidrasi, menjadikannya pilihan populer untuk dimakan segar, dijus, dan hidangan tropis.",
                "fruit_image_preview"=>"https://www.producemarketguide.com/media/user_RZKVrm5KkV/701/pineapple_commodity-page.png",
                "fruit_image_detail"=>"https://img.freepik.com/free-photo/pineapple-whole-half-slices-top-view-white_176474-5368.jpg"
              ],
            [
                "id"=>10,
                "fruit_name"=>"Mangga",
                "fruit_desc"=>"Mangga adalah buah tropis dengan daging manis dan berair serta aroma yang harum. Mangga kaya akan vitamin A, vitamin C, dan serat, yang mendukung kesehatan penglihatan, meningkatkan kekebalan tubuh, dan memperbaiki pencernaan. Mangga sangat disukai untuk dimakan segar, dalam smoothie, atau sebagai bahan dalam salad, hidangan penutup, dan chutney.",
                "fruit_image_preview"=>"https://static.vecteezy.com/system/resources/previews/029/200/087/non_2x/mango-transparent-background-free-png.png",
                "fruit_image_detail"=>"https://media.istockphoto.com/id/1225930816/photo/pattern-of-mango-fruit-isolated-white-background.jpg?s=612x612&w=0&k=20&c=yIZDwLovu2D1DvIaln2MPOS0kjKt8Fk4xMC4fx1Pzsg="
              ],
            [
                "id"=>11,
                "fruit_name"=>"Pepaya",
                "fruit_desc"=>"Pepaya adalah buah tropis dengan daging berwarna oranye, lembut, dan rasa manis. Pepaya kaya akan vitamin C, vitamin A, dan papain, enzim yang membantu pencernaan. Pepaya mendukung kesehatan kulit, meningkatkan kekebalan tubuh, dan dapat membantu mengurangi peradangan. Buah ini dapat dimakan segar, dicampur dalam jus, atau digunakan dalam hidangan gurih.",
                "fruit_image_preview"=>"https://static.vecteezy.com/system/resources/previews/029/332/682/non_2x/papaya-transparent-background-png.png",
                "fruit_image_detail"=>"https://img.freepik.com/premium-photo/top-view-delicious-papaya-fruit-with-copy-space_1234738-555521.jpg"
              ],
            [
                "id"=>12,
                "fruit_name"=>"Semangka",
                "fruit_desc"=>"Semangka adalah buah yang menyegarkan dan menghidrasi dengan daging manis dan berair. Buah ini rendah kalori dan mengandung vitamin A dan C, serta antioksidan seperti likopen, yang mendukung kesehatan jantung. Semangka sangat cocok untuk musim panas, dimakan segar atau digunakan dalam minuman dan salad.",
                "fruit_image_preview"=>"https://cgmood.com/storage/previews/08-2023/167843/167843.jpg",
                "fruit_image_detail"=>"https://www.watermelon.org/wp-content/uploads/2020/07/Seeded-Wedge-scaled.jpg"
              ],
            [
                "id"=>13,
                "fruit_name"=>"Persik",
                "fruit_desc"=>"Persik adalah buah lembut dan berair dengan kulit berbulu dan rasa manis. Buah ini merupakan sumber yang baik dari vitamin C, vitamin A, dan serat makanan, yang mendukung kesehatan kulit dan pencernaan. Persik dinikmati dalam keadaan segar, dalam produk panggang, atau sebagai bahan dalam selai dan salad.",
                "fruit_image_preview"=>"https://media.istockphoto.com/id/1151868959/photo/single-whole-peach-fruit-with-leaf-and-slice-isolated-on-white.jpg?s=612x612&w=0&k=20&c=RLTbnKnN6w85oXn4qA8y8WYN3OMpGxEDc1nI7VY0gWU=",
                "fruit_image_detail"=>"https://www.allrecipes.com/thmb/O4blx_7FgNgOQvmSDmmaHrT8ado=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Peaches-by-nimu1956Getty-Images-2000-5a98227eff4b4cec8ec0ab9d3afd9bc6.jpg"
              ],
            [
                "id"=>14,
                "fruit_name"=>"Kiwi",
                "fruit_desc"=>"Kiwi adalah buah kecil yang asam dengan daging hijau cerah dan biji hitam kecil. Buah ini kaya akan vitamin C, vitamin K, dan kalium, yang mendukung kesehatan kulit, kekebalan tubuh, dan kesehatan jantung. Kiwi umumnya dimakan segar atau digunakan dalam salad buah dan smoothie.",
                "fruit_image_preview"=>"https://www.pngkey.com/png/full/10-100695_free-png-kiwi-fruit-png-png-images-transparent.png",
                "fruit_image_detail"=>"https://media.licdn.com/dms/image/v2/D4D12AQGBCi43PSyv-A/article-cover_image-shrink_600_2000/article-cover_image-shrink_600_2000/0/1654226572903?e=2147483647&v=beta&t=lvsBvGQydWoY24dIR0jTY-97TAIagyNRF8W09r-K1tg"
              ],
            ];

            return response()->json([
                // 'status' => 'OK',
                // 'code' => 200,
                // 'locale' => 'en_US',
                'total' => 14,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve supported fruits: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to retrieve supported fruits list'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/supported-fruits",
     *     tags={"Fruit Detection"},
     *     summary="Get list of supported fruits",
     *     description="Retrieve the list of fruits that can be detected",
     *     operationId="getSupportedFruits",
     *     @OA\Response(
     *         response=200,
     *         description="List of supported fruits",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="OK"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="en_US"),
     *             @OA\Property(property="total", type="integer", example=9),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="diseases_name", type="string"),
     *                     @OA\Property(property="diseases_desc", type="string"),
     *                     @OA\Property(property="dieases_preview", type="string"),
     *                     @OA\Property(property="diseases_detail", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getSupportedFruits()
    {
        try {
            $diseases = [
                [
                    "id" => 1,
                    "diseases_name" => "Penyakit Antraknosa pada papaya",
                    "diseases_desc" => "Penyakit antraknosa disebabkan karena jamur patogen colletotrichum gloeosporioides yang\nmenyerang buah pepaya muda. Penyakit ini ditandai dengan bercak kecil kebasah-basahan,\ndan mengeluarkan getah dengan berupa bintik pada buah maupun daun pepaya. enyakit ini\njuga dipengaruhi oleh kondisi iklim, biasanya di Indonesia penyakit ini kerap menyerang\npada wilayah dengan kondisi curah hujan yang relatif tinggi.\nCiri-ciri Pepaya Terkena Penyakit Antraknosa\nbuah yang terserang penyakit antraknosa ini gejala awalnya bisa diketahui dengan kondisi\njaringan mati yang terlihat sebagai bercak kebasahan.\nJaringan mati tersebut melekuk dan bisa meluas menjadi bercak konsentrik berwarna abu-abu\natau kehitaman dengan bintik-bintik oren pada permukaan buah. Bintik-bintik tersebut bisa\nmenyatu, sehingga menjadi besar.\nPenyakit ini berkembang dengan lambat pada buah yang masih muda, namun menjelang\nmasa panen, penyakit antraknosa ini berkembang dengan pesat.\nPada Daun Pepaya penyakit ini ditandai dengan bercak bulat warna coklat kemerahan yang\nagak mengendap. Selanjutnya jamur membentuk masa spora yang berwarna jingga atau\nmerah muda. Ukurannya bisa membesar dan bercak menyatu.",
                    "dieases_preview" => "https://content.peat-cloud.com/w400/anthracnose-of-papaya-and-mango-papaya-2.jpg",
                    "diseases_detail" => "https://gdm.id/wp-content/uploads/2023/07/penyakit-antraknosa-pada-pepaya-ciri-1.jpg"
                ],
                
      [
        "id"=> 2,
        "diseases_name"=> "Infeksi Botrytis cinereal",
        "diseases_desc"=> "botrytis adalah sekelompok jamur, beberapa di antaranya merusak tanaman dan dapat\nmerusak buah-buahan pertanian seperti anggur. Yang paling umum botrytis jamur adalah\nspesiesnya Botrytis cinerea, Yang juga dikenal sebagai Botryotinia sialanliana, dan cetakan\nabu-abu. Botrytis cinerea muncul sebagai bulu halus berwarna abu-abu pada tanaman dan\ndapat merusak daun, batang, dan buah. Bulu halus berwarna abu-abu sebenarnya disebabkan\noleh spora konidia aseksual jamur ini.\nCiri ciri botrytis cinerea\nTanda-tanda awal Botrytis cinerea termasuk pembentukan bintik-bintik tembus cahaya pada\ntanaman. Bintik-bintik ini bertambah besar dan dapat memakan sebagian besar bagian yang\nterkena. Analisis yang cermat terhadap suatu tanaman dapat mengungkapkan jaringan\nberwarna coklat seperti basah kuyup yang dapat pecah atau pecah. Ciri-ciri ini lebih banyak\nditemukan pada batang dan jaringan sukulen, sedangkan daun biasanya berubah warna\nmenjadi coklat, layu, dan rontok. Bulu halus berwarna abu-abu yang khas muncul pada tahap\nakhir infeksi dan merupakan indikator yang paling jelas Botrytis cinerea. Yang penting, jamur\ntidak selalu berwarna abu-abu tetapi terkadang berwarna coklat atau kemerahan, tergantung\npada tanaman inang dan kondisi lingkungan.",
        "dieases_preview"=> "https://upload.wikimedia.org/wikipedia/commons/e/e4/Aardbei_Lambada_vruchtrot_Botrytis_cinerea.jpg",
        "diseases_detail"=> "https://www.kebun.co.id/wp-content/uploads/2019/11/Panduan-Cara-Pengendalian-Hama-dan-Penyakit-Pada-Strawberry.png"
        ],
      [
        "id"=>3,
        "diseases_name"=>"Kudis Apel",
        "diseases_desc"=>"Kudis apel adalah penyakit yang menyerang tanaman keluarga mawar (Rosaceae), terutama\npohon apel (Malus), dengan ciri-ciri sebagai berikut:\n1. Lesi Gelap pada Daun\na.Muncul bercak gelap berbentuk tidak beraturan pada permukaan daun dan Lesi sering dikelilingi oleh area kuning yang disebut halo.\n2. Infeksi pada Bunga\na.Bunga yang terinfeksi dapat layu atau tidak berkembang dengan baik\n3. Lesi pada Buah\na.Buah yang terkena menunjukkan bercak gelap, kasar, dan berkerak dan Infeksi dapat menyebabkan deformasi bentuk buah.\n4. Gugur Daun dan Buah Prematur\na.Daun dan buah sering rontok sebelum waktunya, terutama pada serangan yang\nparah.\n5. Penurunan Kualitas Buah\na.Buah yang terinfeksi menjadi kurang menarik secara visual dan memiliki nilai\nekonomi rendah.\na.Gejala ini biasanya lebih jelas pada kondisi cuaca yang lembap dan hangat, yang mendukung\nperkembangan jamur Venturia inaequalis.",
        "dieases_preview"=>"https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjlrjsy43sf9cT1jK4FHGgijWPXWODqWrf0Xnp1HL6LAZzC41UzB37LO-T1NR1f0aOBLzT-oRxJpsFDh_Y6rQVwb07FqZ54jn_OzOwd9f-Fmk_eksysFtfh4aQ9tXpDubr1_CcM3zMv6iqQ/s320/1.png",
        "diseases_detail"=>"https://st4.depositphotos.com/1001345/25438/i/450/depositphotos_254384854-stock-photo-apple-scab-disease.jpg"
        ],
      [
        "id"=>4,
        "diseases_name"=>"Bercak Buah Jambu",
        "diseases_desc"=>"Bercak pada buah jambu dapat disebabkan oleh penyakit bercak daun dan buah alga, yang\ndiakibatkan oleh alga Cephaleuros virescens Kuntze. Gejala penyakit ini meliputi:\n1. Daun\na.Pada awal musim semi, alga menginfeksi daun muda jambu.\nb.Muncul bercak cokelat dangkal dengan tekstur beludru pada daun, terutama di ujung,\ntepi, atau dekat tulang daun utama.\nc.Seiring perkembangan penyakit, bercak tersebut dapat membesar hingga berdiameter\n2-3 mm.\n2. Buah\na.Pada buah muda, bercak tampak hampir hitam.\nb.Saat buah tumbuh, bercak menjadi cekung dan sering retak.\nc.Bercak pada buah biasanya lebih kecil dibandingkan bercak pada daun, dengan warna\nhijau gelap, cokelat, atau hitam.\n\nPenyakit ini dapat memengaruhi kualitas daun dan buah, terutama pada tanaman yang\ntumbuh di lingkungan lembap.",
        "dieases_preview"=>"https://asset.kompas.com/crops/AxKm2HqhcBHoMOzbq9lDHkkc44Y=/4x96:367x459/340x340/data/photo/2023/01/15/63c3887631a52.jpg",
        "diseases_detail"=>"https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQufpl3ep7M3_Ci9wnX5z2d0qVIUmU3o5zJSQ&s"
        ],
      [
        "id"=>5,
        "diseases_name"=>"Penyakit Phytophthora pada Pepaya",
        "diseases_desc"=>"Penyakit Phytophthora merupakan salah satu ancaman utama bagi tanaman pepaya. Penyakit\nini disebabkan oleh patogen Phytophthora palmivora, yang menyerang berbagai bagian\ntanaman seperti akar, batang, daun, dan buah. Kondisi lingkungan yang lembap dan hangat\nsangat mendukung perkembangan penyakit ini.\nGejala Utama:\n1. Busuk Akar dan Pangkal Batang\na.Tanaman menunjukkan gejala layu mendadak.\nb.Bagian pangkal batang membusuk dan mengeluarkan bau tidak sedap.\n2. Busuk Daun\na.Daun yang terinfeksi menjadi layu dan menguning.\nb.Dalam kasus yang parah, daun dapat gugur sepenuhnya.\n3. Busuk Buah\na.Muncul bercak cokelat gelap hingga hitam pada permukaan buah.\nb.Buah yang terinfeksi sering membusuk dan rontok sebelum matang.\n\nPengendalian:\n1. Kultur Teknis:\na.Pastikan drainase lahan baik untuk mencegah genangan air.\nb.Hindari penanaman terlalu rapat agar sirkulasi udara baik.\n2. Penggunaan Fungisida:\na.Gunakan fungisida berbahan aktif seperti fosetil-Al atau metalaksil secara\nterukur dan tepat waktu.\n3. Pencegahan:\na.Gunakan bibit yang sehat dan tahan penyakit.\nb.Lakukan sanitasi kebun secara rutin untuk mengurangi sumber infeksi.\n\nPengelolaan penyakit Phytophthora memerlukan perhatian serius karena dapat menyebabkan\nkerugian ekonomi yang besar pada budidaya pepaya, terutama dalam kondisi lingkungan\nyang mendukung penyebaran patogen ini.",
        "dieases_preview"=>"https://content.peat-cloud.com/w400/phytophthora-crown-and-root-rot-papaya-1666624647.jpg",
        "diseases_detail"=>"https://digrow.co.id/wp-content/uploads/2022/05/Foto-18.-Busuk-Akar-dan-Pangkal-Batang-Phytophthora-palmivora-dan-Pythium-sp..jpg"
        ],
      [
        "id"=>6,
        "diseases_name"=>"Apple Blotch Penyakit Pada Pohon Apel",
        "diseases_desc"=>"Apple blotch adalah penyakit yang menyerang tanaman apel, disebabkan oleh jamur Marssonina\ncoronaria. Penyakit ini sering ditemukan di daerah dengan iklim lembap dan dapat menyebabkan\nkerugian signifikan pada hasil panen apel.\nGejala Utama:\n1. Daun:\na. Muncul bercak kecil berwarna cokelat gelap hingga hitam di permukaan daun.\nb. Bercak dikelilingi oleh area kuning (halo).\nc. Infeksi parah dapat menyebabkan daun rontok sebelum waktunya (defoliasi).\n2. Buah:\na. Bercak gelap dan tidak beraturan muncul di permukaan buah.\nb. Buah yang terinfeksi seringkali memiliki tekstur kasar dan tampak cacat.\nc. Infeksi berat dapat menyebabkan buah rontok sebelum matang.\n3. Batang:Pada beberapa kasus, bercak atau luka kecil juga terlihat pada ranting muda.\nPenyebab dan Faktor Pemicu:\na. Jamur Marssonina coronaria menyebar melalui spora yang terbawa angin atau percikan air\nhujan.\nb. Lingkungan yang lembap dan suhu hangat sangat mendukung perkembangan jamur ini.\nc. Sisa-sisa daun yang terinfeksi di tanah dapat menjadi sumber utama infeksi di musim\nberikutnya.\nPengendalian dan Pencegahan:\nKultur Teknis:\nLakukan pemangkasan pohon untuk meningkatkan sirkulasi udara.\nBersihkan sisa daun atau buah yang jatuh untuk mengurangi sumber infeksi.\nFungisida:\nGunakan fungisida berbahan aktif seperti mankozeb atau tembaga oksiklorida sesuai dosis\nyang dianjurkan.\nPengelolaan Kebun:\nPastikan drainase kebun baik untuk menghindari kelembapan berlebih.\nTanam varietas apel yang lebih tahan terhadap penyakit ini.\nPenyakit apple blotch perlu dikendalikan secara efektif untuk menjaga kesehatan tanaman dan\nkualitas buah apel yang dihasilkan. Upaya pencegahan lebih diutamakan karena penyakit ini\ndapat menyebar dengan cepat dalam kondisi lingkungan yang mendukung",
        "dieases_preview"=>"https://content.peat-cloud.com/w400/apple-scab-apple-4.jpg",
        "diseases_detail"=>"https://gdm.id/wp-content/uploads/2023/07/penyakit-busuk-buah-apel-1.jpg"
        ],
      [
        "id"=> 7,
        "diseases_name"=> "Kumbang Penggaris Pisang (Banana Scarring Beetle): Hama Utama Tanaman\nPisang",
        "diseases_desc"=>"Kumbang penggaris pisang adalah hama penting yang menyerang tanaman pisang,\nmenyebabkan kerusakan pada kulit buah yang memengaruhi kualitas dan nilai jualnya. Hama\nini disebabkan oleh kumbang dari famili Chrysomelidae, dengan gejala kerusakan yang khas\npada buah pisang.\nCiri-Ciri Serangan:\n1. Kerusakan pada Kulit Buah:\na.Muncul bekas gigitan berupa goresan atau garis-garis melintang pada kulit buah.\nb.Goresan ini biasanya berwarna cokelat kehitaman dan kasar saat disentuh.\n2. Pengaruh pada Buah:\na.Kulit buah pisang tampak cacat atau rusak secara visual.\nb.Meski daging buah tidak terpengaruh secara langsung, kerusakan kulit menurunkan\nnilai jual.\n3. Tanaman Lain yang Diserang:\na.Selain pisang, kumbang ini juga dapat menyerang tanaman lain di sekitar kebun\npisang, terutama tanaman yang memiliki kulit buah lembut.\nSiklus Hidup:\nKumbang dewasa bertelur di permukaan buah pisang atau bagian tanaman lainnya.\nLarva menetas dan mulai memakan kulit buah, menyebabkan kerusakan khas.\nDalam kondisi lingkungan yang hangat dan lembap, siklus hidup kumbang berlangsung\ndengan cepat, memungkinkan populasi berkembang pesat.\nPengendalian dan Pencegahan:\n1. Pengelolaan Kebun:\na.Jaga kebersihan kebun dengan membersihkan sisa-sisa tanaman dan buah yang jatuh.\nb.Hindari menanam pisang terlalu rapat untuk meningkatkan sirkulasi udara.\n2. Metode Fisik:\na.Bungkus buah pisang dengan kantong plastik atau kain pelindung untuk mencegah\nserangan kumbang.\n3. Pengendalian Kimia:\na.Gunakan insektisida berbahan aktif seperti imidakloprid atau deltametrin sesuai dosis\nyang dianjurkan.\nb.Lakukan penyemprotan secara selektif pada area yang terdampak.\n4. Pengendalian Biologis:\na.Introduksi predator alami kumbang, seperti burung atau serangga pemangsa, untuk\nmengendalikan populasi secara alami.\n\nSerangan kumbang penggaris pisang dapat dicegah dengan pengelolaan kebun yang baik dan\npenggunaan metode pengendalian yang tepat. Upaya ini penting untuk memastikan produksi\npisang tetap berkualitas tinggi dan layak dipasarkan",
        "dieases_preview"=> "https://plantwiseplusknowledgebank.org/cms/10.1079/pwkb.20167801172/asset/08b7b5b8-35d2-46f7-8bdc-6da45859767a/assets/graphic/colaspis-on-banana-costa-rica-3.png",
        "diseases_detail"=> "https://content.peat-cloud.com/w400/banana-fruit-scarring-beetle-banana-1573052781.jpg"
        ],
      [
        "id"=> 8,
        "diseases_name"=> "Citrus Black Spot: Penyakit Serius pada Tanaman Jeruk",
        "diseases_desc"=> "Citrus black spot adalah penyakit yang menyerang tanaman jeruk, disebabkan oleh jamur\nPhyllosticta citricarpa (sebelumnya dikenal sebagai Guignardia citricarpa). Penyakit ini\ndapat menurunkan kualitas buah secara signifikan dan menyebabkan kerugian ekonomi,\nterutama di daerah dengan kelembapan tinggi.\nGejala Citrus Black Spot\n1. Bercak Hitam pada Buah:\na.Muncul bercak hitam kecil di permukaan kulit buah.\nb.Bercak ini bisa berkembang menjadi area gelap yang lebih besar dengan pusat\nberwarna abu-abu.\n2. Bercak Kemerahan (Red Spot):\na.Kadang-kadang terlihat bercak merah keunguan pada buah muda yang kemudian\nmengering.\n3. Kerak Keras (Hard Spot):\na.Lesi keras yang sering terasa kasar saat disentuh, dikelilingi oleh area kuning.\n4. Buah Prematur Rontok:\na.Pada infeksi parah, buah sering rontok sebelum matang.5. Penurunan Kualitas Buah:\nb.Buah menjadi tidak menarik untuk dijual atau dikonsumsi karena tampilan yang\ncacat.\nPenyebab dan Penyebaran\na.Jamur Phyllosticta citricarpa menghasilkan spora yang tersebar melalui angin, air hujan, atau\nsisa-sisa tanaman yang terinfeksi.\nb.Penyakit ini lebih umum terjadi di daerah dengan kelembapan tinggi dan suhu hangat.",
        "dieases_preview"=> "https://www.millerchemical.com/wp-content/uploads/2022/03/image-1.png",
        "diseases_detail"=> "https://www.millerchemical.com/wp-content/uploads/2022/03/image.png"
        ],
      [
        "id"=> 9,
        "diseases_name"=> "Mango Sapburn: Masalah yang Sering Terjadi pada Buah Mangga",
        "diseases_desc"=> "Mango sapburn adalah kondisi yang sering dialami buah mangga akibat paparan getah (sap)\nyang mengandung senyawa kimia iritan. Getah ini keluar dari tangkai buah saat dipetik dan\ndapat menyebabkan luka atau bercak pada kulit buah, sehingga menurunkan kualitas\nvisualnya.\nPenyebab Mango Sapburn :\na.Komponen Kimia dalam Getah Mangga:\nGetah mangga mengandung senyawa seperti asam organik, alkaloid, dan terpenoid yang bersifat\niritan.Ketika terkena kulit buah, senyawa ini dapat merusak lapisan luar kulit, menyebabkan bercak cokelat\natau luka bakar (burn).\nCara Penanganan yang Tidak Tepat:\nPemetikan buah secara sembarangan sering menyebabkan getah menyebar ke permukaan buah.\nPenyimpanan tanpa ventilasi yang baik dapat memperburuk efek getah.",
        "dieases_preview"=> "https://www.researchgate.net/profile/Nahmagal-Krishnapillai/publication/353480390/figure/fig2/AS:1050066341081088@1627366733116/External-appearance-of-sap-injured-Karuthakolumban-and-Willard-mangoes_Q320.jpg",
        "diseases_detail"=>"https://i.ytimg.com/vi/4QvvCmw965Q/maxresdefault.jpg"
        ],
              ];

            return response()->json([
                // 'status' => 'OK',
                // 'code' => 200,
                // 'locale' => 'en_US',
                'total' => 9,
                'data' => $diseases
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve supported fruits: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to retrieve supported fruits list'
            ], 500);
        }
    }
}