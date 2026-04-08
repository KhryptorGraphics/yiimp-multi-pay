#include "stratum.h"

#include <array>
#include <cstring>

static constexpr size_t SPACESCRYPT_HEADER_SIZE = 80;
static constexpr size_t SPACESCRYPT_CHUNK_SIZE = 16;
static constexpr size_t SPACESCRYPT_ARENA_SIZE = 256;

static inline uint64_t spacescrypt_le64dec(const unsigned char *src)
{
	return (uint64_t)src[0]
		| ((uint64_t)src[1] << 8)
		| ((uint64_t)src[2] << 16)
		| ((uint64_t)src[3] << 24)
		| ((uint64_t)src[4] << 32)
		| ((uint64_t)src[5] << 40)
		| ((uint64_t)src[6] << 48)
		| ((uint64_t)src[7] << 56);
}

static inline void spacescrypt_le32enc(unsigned char *dst, uint32_t value)
{
	dst[0] = (unsigned char)(value & 0xff);
	dst[1] = (unsigned char)((value >> 8) & 0xff);
	dst[2] = (unsigned char)((value >> 16) & 0xff);
	dst[3] = (unsigned char)((value >> 24) & 0xff);
}

static inline void spacescrypt_sha256d(unsigned char *hash, const unsigned char *input, size_t len)
{
	sha256_double_hash((const char *)input, (char *)hash, (unsigned int)len);
}

static inline void spacescrypt_mix(unsigned char *out, const unsigned char *left,
	const unsigned char *right, const unsigned char *chunk, size_t chunk_size,
	uint32_t tweak)
{
	unsigned char buf[32 + 32 + SPACESCRYPT_HEADER_SIZE + 4];
	unsigned char tweak_bytes[4];

	memcpy(buf, left, 32);
	memcpy(buf + 32, right, 32);
	memcpy(buf + 64, chunk, chunk_size);
	spacescrypt_le32enc(tweak_bytes, tweak);
	memcpy(buf + 64 + chunk_size, tweak_bytes, sizeof(tweak_bytes));
	spacescrypt_sha256d(out, buf, 64 + chunk_size + sizeof(tweak_bytes));
}

void spacescrypt_1024_1_1_256(const unsigned char *input, unsigned char *output)
{
	static const uint32_t SPACESCRYPT_STSP = 0x53545350U;
	static const uint32_t SPACESCRYPT_SPCE = 0x53504345U;
	static const uint32_t SPACESCRYPT_ROCK = 0x524f434bU;
	static const uint32_t SPACESCRYPT_FLYT = 0x464c5954U;

	unsigned char base_hash[32];
	unsigned char header[SPACESCRYPT_HEADER_SIZE];
	unsigned char header_hash[32];
	unsigned char state[32];
	unsigned char final_hash[32];
	std::array<std::array<unsigned char, 32>, SPACESCRYPT_ARENA_SIZE> arena;

	memcpy(header, input, sizeof(header));
	scrypt_1024_1_1_256((unsigned char *)input, base_hash);
	spacescrypt_sha256d(header_hash, header, sizeof(header));

	spacescrypt_mix(arena[0].data(), base_hash, header_hash, header,
		SPACESCRYPT_HEADER_SIZE, SPACESCRYPT_STSP);
	for (size_t i = 1; i < SPACESCRYPT_ARENA_SIZE; ++i) {
		const size_t offset = (i * 13)
			% (SPACESCRYPT_HEADER_SIZE - SPACESCRYPT_CHUNK_SIZE + 1);
		spacescrypt_mix(arena[i].data(), arena[i - 1].data(), base_hash,
			header + offset, SPACESCRYPT_CHUNK_SIZE, (uint32_t)i);
	}

	spacescrypt_mix(state, base_hash, header_hash, header + 8, 32, SPACESCRYPT_SPCE);
	for (size_t round = 0; round < SPACESCRYPT_ARENA_SIZE; ++round) {
		const uint32_t lane = (uint32_t)(
			(spacescrypt_le64dec(state + ((round & 3) * 8))
			 ^ spacescrypt_le64dec(arena[round].data() + (((round + 1) & 3) * 8))
			 ^ (uint64_t)round)
			& (SPACESCRYPT_ARENA_SIZE - 1));
		const size_t offset = (lane + (round * 7))
			% (SPACESCRYPT_HEADER_SIZE - SPACESCRYPT_CHUNK_SIZE + 1);

		spacescrypt_mix(state, state, arena[lane].data(), header + offset,
			SPACESCRYPT_CHUNK_SIZE, SPACESCRYPT_ROCK ^ (uint32_t)round);
		spacescrypt_mix(arena[lane].data(), arena[lane].data(), arena[round].data(),
			header + offset, SPACESCRYPT_CHUNK_SIZE,
			SPACESCRYPT_FLYT + (uint32_t)round);
	}

	{
		unsigned char final_buf[32 * 5];
		memcpy(final_buf, state, 32);
		memcpy(final_buf + 32, base_hash, 32);
		memcpy(final_buf + 64, arena[0].data(), 32);
		memcpy(final_buf + 96, arena[SPACESCRYPT_ARENA_SIZE / 2].data(), 32);
		memcpy(final_buf + 128, arena[SPACESCRYPT_ARENA_SIZE - 1].data(), 32);
		spacescrypt_sha256d(final_hash, final_buf, sizeof(final_buf));
	}

	memcpy(output, final_hash, 32);
}
