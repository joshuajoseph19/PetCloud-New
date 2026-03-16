<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Vet - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        h1 {
            font-family: 'Outfit';
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .search-vet {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            background: #3b82f6;
            color: white;
        }

        .vet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .vet-card {
            border: 1px solid #f3f4f6;
            padding: 1.25rem;
            border-radius: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .vet-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .vet-info h3 {
            margin: 0 0 0.25rem 0;
            font-family: 'Outfit';
            font-size: 1.1rem;
        }

        .vet-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="health-records.php"
            style="color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 0.9rem;"><i
                class="fa-solid fa-arrow-left"></i> Back</a>
        <h1 style="margin-top: 1rem;">Veterinary Clinics Near You</h1>
        <div class="search-vet">
            <input type="text" placeholder="Enter zip code or city...">
            <button class="btn">Find Clinics</button>
        </div>

        <div class="vet-grid">
            <div class="vet-card">
                <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=200" class="vet-img">
                <div class="vet-info">
                    <h3>Paws & Claws Medical</h3>
                    <p>123 Pet Blvd, San Francisco</p>
                    <span class="badge badge-green">Open Now</span>
                </div>
            </div>
            <div class="vet-card">
                <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=200" class="vet-img">
                <div class="vet-info">
                    <h3>Happy Tails Hospital</h3>
                    <p>456 Bark Lane, San Francisco</p>
                    <span class="badge badge-green">Open Now</span>
                </div>
            </div>
            <div class="vet-card">
                <img src="https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=200" class="vet-img">
                <div class="vet-info">
                    <h3>Ocean Side Vet</h3>
                    <p>789 Shore Way, San Francisco</p>
                    <span class="badge" style="background:#fee2e2; color:#991b1b;">Closing Soon</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>