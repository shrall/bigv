<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add New Address</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="container mb-5">
        <div class="mt-5 d-flex justify-content-between">
            <a href="{{ route('user.user-address.index') }}" class="btn btn-outline-secondary"><span><i data-feather="arrow-left"></i></span> Back</a>
        </div>
        <div class="border rounded px-5 py-3 mt-3">
            <div class="mb-3 row">
                <h1>Add New Address</h1>
            </div>
            <hr>
            <form method="POST" action="{{ route('user.user-address.store') }}">
                @csrf

                <div class="mb-3 row">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') 'is-invalid' @enderror" id="name" name="name" value="{{ old('name') }}">

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">

                    @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="additionalInfo" class="form-label">Additional Info</label>
                    <textarea class="form-control" id="additionalInfo" name="additional_info" rows="3">{{ old('additional_info') }}</textarea>
                
                    @error('additional_info')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="street" class="form-label">Street</label>
                    <input type="text" class="form-control" id="street" name="street" value="{{ old('street') }}">

                    @error('street')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="condo" class="form-label">Condo</label>
                    <input type="text" class="form-control" id="condo" name="condo" value="{{ old('condo') }}">

                    @error('condo')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="estate" class="form-label">Estate</label>
                    <input type="text" class="form-control" id="estate" name="estate" value="{{ old('estate') }}">

                    @error('estate')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="label" class="form-label">Label</label>
                    <input type="text" class="form-control" id="label" name="label" value="{{ old('label') }}">

                    @error('label')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="houseNumber" class="form-label">House Number</label>
                    <input type="text" class="form-control" id="houseNumber" name="house_number" value="{{ old('house_number') }}">

                    @error('house_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="unitNumber" class="form-label">Unit Number</label>
                    <input type="text" class="form-control" id="unitNumber" name="unit_number" value="{{ old('unit_number') }}">

                    @error('unit_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" maxlength="6" id="postalCode" name="postal_code" value="{{ old('postal_code') }}">

                    @error('postal_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3 row">
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script>
        feather.replace()
    </script>
</body>
</html>